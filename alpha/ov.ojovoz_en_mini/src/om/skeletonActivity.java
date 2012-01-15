/*
*ojoVoz: Alpha version 2011-2012 by Eugenio Tisselli
*Please refer to the file gpl-3.0
*to view terms of GPL licence
*/

package om;

import java.io.File;
import java.io.IOException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;

import om.skeleton.R;
import android.app.Activity;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.SharedPreferences;
import android.database.Cursor;
import android.graphics.Bitmap;
import android.location.Location;
import android.location.LocationListener;
import android.location.LocationManager;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.net.Uri;
import android.os.Bundle;
import android.os.Environment;
import android.os.Handler;
import android.os.Message;
import android.provider.MediaStore;
import android.view.MenuItem;
import android.view.View;
import android.widget.AdapterView;
import android.widget.AdapterView.OnItemSelectedListener;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;
import android.graphics.Color;

public class skeletonActivity extends Activity {
	private static final int CAPTURE_IMAGE_ACTIVITY_REQUEST_CODE = 100;

	private Uri photoUri;
	private boolean photoDone;

	private AudioRecorder soundRecorder = new AudioRecorder();
	private Boolean recording = false;
	private boolean recordingDone;

	private String messageDate;

	private String tags[];
	private String tag;
	private Spinner tagSpinner;

	private String user="";
	private PromptDialog dlg = null;

	private String server="";
	private String phone_id="";

	private LocationManager lm;
	private LocationListener locationListener;
	private Location currentBestLocation=null;
	private double lat = -1;
	private double lon = -1;
	private static final int TWO_MINUTES = 1000 * 60 * 2;
	private static final int FIVE_MINUTES = 1000 * 60 * 5;
	private long lastGPSFix = -1;
	private Timer gpsTimer;

	private ProgressDialog dialog;
	private int uploadIncrement=1;
	private Thread upload;
	private boolean cancelUpload;
	private boolean sending=false;

	/** Called when the activity is first created. */
	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.main);

		createDir("ojovoz");
		messageDate=new SimpleDateFormat("dd_MM_yyyy_kk_mm_ss").format(new Date()).toString();
		lm = (LocationManager)getSystemService(Context.LOCATION_SERVICE);    
		locationListener = new OMLocationListener();
		lm.requestLocationUpdates(LocationManager.GPS_PROVIDER,5000,5,locationListener);
		gpsTimer = new Timer(10000, new Runnable(){
			public void run() {
				if(skeletonActivity.this.lastGPSFix>0){
					long m = Calendar.getInstance().getTimeInMillis();
					if(Math.abs(skeletonActivity.this.lastGPSFix - m)>FIVE_MINUTES) {
						skeletonActivity.this.resetPosition();
					}
				}
			}
		});
		gpsTimer.start();

		server = getPreference("server");
		if (server.equals("")) {
			defineServer("");
		} 

		phone_id=getPreference("id");
		if (phone_id.equals("")) {
			definePhoneId("");
		}

		getTags();

		user = getPreference("user");

		tag="";
		String tagList = "Choose a tag;" + getPreference("tags") + ";Enter new tag:";
		tags = tagList.split(";");
		tagSpinner = (Spinner)findViewById(R.id.omTags);
		ArrayAdapter<String> adapter = new ArrayAdapter<String>(this,android.R.layout.simple_spinner_dropdown_item, tags);
		tagSpinner.setAdapter(adapter);
		tagSpinner.setOnItemSelectedListener(new OnItemSelectedListener(){
			public void onItemSelected(AdapterView<?> arg0, View arg1, int arg2, long arg3){
				int i=tagSpinner.getSelectedItemPosition();
				if (i==(tags.length-1)) {
					EditText newTag = (EditText)findViewById(R.id.omNewTag);
					newTag.setVisibility(0);
					tag="*";
				}else if(i>0){
					tag = tags[i];
					EditText newTag = (EditText)findViewById(R.id.omNewTag);
					newTag.setVisibility(4);
				} else {
					tag="";
					EditText newTag = (EditText)findViewById(R.id.omNewTag);
					newTag.setVisibility(4);
				}
			}

			public void onNothingSelected(AdapterView<?> arg0) {}
		});
		
		final EditText newTag = (EditText)findViewById(R.id.omNewTag);
		newTag.setOnClickListener(new View.OnClickListener() {
			
			@Override
			public void onClick(View v) {
				newTag.selectAll();
			}
		});

		photoDone=false;
		recordingDone=false;
	}
	
	@Override
	public void onBackPressed() {
		super.onBackPressed();
		if(recording){
			try {
				soundRecorder.stop();
			} catch (IOException e) {
			}
		}
	}

	public void getTags() {
		String ret = doHTTPRequest(server + "/mobile/get_tags.php?id=" + phone_id);
		if (ret!=null && ret!="") {
			savePreference("tags",ret,false);
		}
	}
	
	public void deleteMessages() {
		String allMessages[];
		String bundledMessages=getPreference("log");
		if(bundledMessages != "" && bundledMessages != null) {
			allMessages=bundledMessages.split("\\*");
			for(int i=0;i<allMessages.length;i++) {
				String thisMessage=allMessages[i];
				if(thisMessage != "" && thisMessage != null){
					String messageElements[] = thisMessage.split(";");
					deleteFileOM(messageElements[4]);
					deleteFileOM(messageElements[5]);	
				}
			}
			savePreference("log","",false);
		}
	}

	@Override
	public boolean onCreateOptionsMenu(android.view.Menu menu) {
		super.onCreateOptionsMenu(menu);
		menu.add(0, 0, 0, "Send messages");
		menu.add(1, 1, 1, "My name");
		return true;
	}

	@Override
	public boolean onOptionsItemSelected(MenuItem item) {
		// Handle item selection
		switch (item.getItemId()) {
		case 0:
			if(!sending && !recording){
				sendMessages();
			}
			break;
		case 1:
			if(!sending && !recording){
				dlg = new PromptDialog(this, R.string.omDialogTitle, R.string.omTextFieldLabel, user) {
					@Override
					public boolean onOkClicked(String input) {
						if(input.equals("admin")){
							defineServer(skeletonActivity.this.server);
							definePhoneId(skeletonActivity.this.phone_id);
						} else if(input.equals("delete")) {
							deleteMessages();
						} else {
							skeletonActivity.this.user = input;
							savePreference("user",input,false);
						}
						return true;
					}
				};
				dlg.show();
			}
			break;
		}
		return super.onOptionsItemSelected(item);
	}

	public void defineServer(String current) {
		dlg = new PromptDialog(this, R.string.omDefineServerTitle, R.string.omServerLabel, current) {
			@Override
			public boolean onOkClicked(String input) {
				skeletonActivity.this.server = input;
				//createDir("/om");
				savePreference("server",input,false);
				return true;
			}
		};
		dlg.show();
	}

	public void definePhoneId(String current) {
		dlg = new PromptDialog(this, R.string.omDefineIdTitle, R.string.omIdLabel, current) {
			@Override
			public boolean onOkClicked(String input) {
				skeletonActivity.this.phone_id = input;
				//createDir("/om");
				savePreference("id",input,false);
				return true;
			}
		};
		dlg.show();
	}

	public void createDir(String dir) {
		File f = new File(Environment.getExternalStorageDirectory().getAbsolutePath() + dir);
		if(!f.isDirectory()) {
			f.mkdir();
		}

	}
	
	public String getPreference(String keyName){
		String value="";
		SharedPreferences ojoVozPrefs = getSharedPreferences("ojoVozPrefs", MODE_PRIVATE);
		value=ojoVozPrefs.getString(keyName, "");
		return value;
	}
	
	public void savePreference(String keyName,String keyValue,boolean append){
		SharedPreferences ojoVozPrefs = getSharedPreferences("ojoVozPrefs", MODE_PRIVATE);
		SharedPreferences.Editor prefEditor = ojoVozPrefs.edit();
		if(append){
			keyValue=ojoVozPrefs.getString(keyName, "") + keyValue;
		}
		prefEditor.putString(keyName, keyValue);
		prefEditor.commit();
	}

	/*
	public void saveFile(String path,String contents,boolean append) {
		boolean save=false;
		String filePath = Environment.getExternalStorageDirectory().getAbsolutePath() + path;
		File writeFile = new File(filePath);
		if (!writeFile.exists()) {
			try {
				writeFile.createNewFile();
				save=true;
			} catch (Exception e) {

			}
		} else {
			save=true;
		}

		if(save) {
			try {
				BufferedWriter buf = new BufferedWriter(new FileWriter(writeFile, append));
				if (append) {
					buf.append(contents);
				} else {
					buf.write(contents);
				}
				buf.close();
			} catch (IOException e) {
				//
			}
		}
	}
	*/

	public void showRecorder(View v) {
		if (!recording) {
			if (!soundRecorder.getFilename().equals("null")){
				deleteFileOM(soundRecorder.getFilename());
			}
			soundRecorder.modifyPath("ojovoz/s" + messageDate); 
			try {
				Button buttonRecording = (Button)findViewById(R.id.omVoiceButton);
				buttonRecording.setText("Recording voice ...");
				buttonRecording.setTextColor(Color.RED);
				TextView textSoundRecorded = (TextView)findViewById(R.id.textSoundRecorded);
				textSoundRecorded.setText("");
				Button buttonSave = (Button)findViewById(R.id.omSaveButton);
				buttonSave.setVisibility(4);
				tagSpinner.setVisibility(4);
				soundRecorder.start();
				recording = true;
			} catch (IOException e) {
				//
			}
		} else {
			if (soundRecorder != null) {
				try {
					Button buttonRecording = (Button)findViewById(R.id.omVoiceButton);
					buttonRecording.setText("Record voice");
					buttonRecording.setTextColor(Color.BLACK);
					soundRecorder.stop();
					recording = false;
					recordingDone=true;
					tagSpinner.setVisibility(0);
					if(photoDone) {
						Button saveButton = (Button)findViewById(R.id.omSaveButton);
						saveButton.setVisibility(0);
					}
					TextView textSoundRecorded = (TextView)findViewById(R.id.textSoundRecorded);
					textSoundRecorded.setText("Voice recorded");
				} catch (IOException e) {
					//
				}
			}
		}
	}

	public void showCamera(View v) {
		// create Intent to take a picture and return control to the calling application

		//String photoName = Environment.getExternalStorageDirectory() + File.separator + "om/i" + messageDate + ".jpg";

		//ContentValues values = new ContentValues();
		//values.put(MediaStore.Images.Media.TITLE, photoName);

		//photoUri = getContentResolver().insert(MediaStore.Images.Media.EXTERNAL_CONTENT_URI, values);

		Intent intent = new Intent(MediaStore.ACTION_IMAGE_CAPTURE);
		//intent.putExtra(MediaStore.EXTRA_OUTPUT, photoUri);
		// start the image capture Intent
		startActivityForResult(intent, CAPTURE_IMAGE_ACTIVITY_REQUEST_CODE);
	}

	public void saveMessage(View v) {
		String writeText;
		String photoFile;
		String sUser;
		String sTag;
		if(photoUri==null) {
			photoFile="null";
		} else {
			photoFile=getRealPathFromURI(photoUri);
		}
		if (user.equals("") || user==null) {
			sUser="default";
		} else {
			sUser = user;
		}
		if (tag.equals("")) {
			sTag="null";
		} else {
			if(tag=="*"){
				EditText newTag = (EditText)findViewById(R.id.omNewTag);
				sTag=newTag.getText().toString();
				if (sTag.equals("")){
					sTag="null";
				}
			} else {
				sTag=tag;
			}
		}
		messageDate=new SimpleDateFormat("dd_MM_yyyy_kk_mm_ss").format(new Date()).toString();
		writeText = sUser + ";" + messageDate + ";" + lat + ";" + lon + ";" + photoFile + ";" + soundRecorder.getFilename() + ";" + sTag + "*";
		savePreference("log",writeText,true);

		soundRecorder.clear();
		photoUri = null;
		Button buttonRecording = (Button)findViewById(R.id.omVoiceButton);
		buttonRecording.setText("Record voice");
		buttonRecording.setTextColor(Color.BLACK);
		TextView textRecording = (TextView)findViewById(R.id.textSoundRecorded);
		textRecording.setText("");
		ImageView imageView = (ImageView)findViewById(R.id.omThumb);
		imageView.setImageResource(R.drawable.omBlank);
		Button buttonSave = (Button)findViewById(R.id.omSaveButton);
		buttonSave.setVisibility(4);
		EditText newTag = (EditText)findViewById(R.id.omNewTag);
		newTag.setVisibility(4);
		photoDone=false;
		recordingDone=false;
		tag="";
		tagSpinner.setSelection(0);
		Toast.makeText(this, "Message saved",Toast.LENGTH_SHORT).show();

	}

	public String getRealPathFromURI(Uri contentUri) {
		
		/*
		String[] proj = { MediaStore.Images.Media.DATA };
		Cursor cursor = managedQuery(contentUri, proj, null, null, null);
		int column_index = cursor.getColumnIndexOrThrow(MediaStore.Images.Media.DATA);
		cursor.moveToFirst();
		return cursor.getString(column_index);
		*/
		String realPath="";
		Cursor cursor;
		String[] proj = { MediaStore.Images.ImageColumns._ID, MediaStore.Images.ImageColumns.DATA };
		String largeFileSort = MediaStore.Images.ImageColumns._ID + " DESC";
		try{
			cursor = managedQuery(contentUri, proj, null, null, largeFileSort);
			cursor.moveToFirst();
			realPath = cursor.getString(cursor.getColumnIndexOrThrow(MediaStore.Images.Media.DATA));
			cursor.close();
		} catch(Exception e) {
			
		}
		return realPath;
	}

	@Override
	protected void onActivityResult(int requestCode, int resultCode, Intent data) {
		if (requestCode == CAPTURE_IMAGE_ACTIVITY_REQUEST_CODE) {
			if (resultCode == RESULT_OK) {
				// Image captured and saved to fileUri specified in the Intent
				//Uri selectedPhoto = photoUri;
				Uri selectedPhoto = data.getData();
				if (selectedPhoto == null) {
					long imageId = 0l;
					String[] projection = {
							MediaStore.Images.Thumbnails._ID,  // The columns we want
							MediaStore.Images.Thumbnails.IMAGE_ID,
							MediaStore.Images.Thumbnails.KIND,
							MediaStore.Images.Thumbnails.DATA};
					String selection = MediaStore.Images.Thumbnails.KIND + "="  + // Select only mini's
					MediaStore.Images.Thumbnails.MINI_KIND;
					String sort = MediaStore.Images.Thumbnails._ID + " DESC";
					Cursor myCursor = this.managedQuery(MediaStore.Images.Thumbnails.EXTERNAL_CONTENT_URI, projection, selection, null, sort);
					//long thumbnailImageId = 0l;
					//String thumbnailPath = "";
					try{
						myCursor.moveToFirst();
						imageId = myCursor.getLong(myCursor.getColumnIndexOrThrow(MediaStore.Images.Thumbnails.IMAGE_ID));
						//thumbnailImageId = myCursor.getLong(myCursor.getColumnIndexOrThrow(MediaStore.Images.Thumbnails._ID));
						//thumbnailPath = myCursor.getString(myCursor.getColumnIndexOrThrow(MediaStore.Images.Thumbnails.DATA));
					}
					finally{myCursor.close();}
					/*
					String[] largeFileProjection = {
							MediaStore.Images.ImageColumns._ID,
							MediaStore.Images.ImageColumns.DATA
					};

					String largeFileSort = MediaStore.Images.ImageColumns._ID + " DESC";
					myCursor = this.managedQuery(MediaStore.Images.Media.EXTERNAL_CONTENT_URI, largeFileProjection, null, null, largeFileSort);
					//String largeImagePath = "";

					try{
						myCursor.moveToFirst();
						//largeImagePath = myCursor.getString(myCursor.getColumnIndexOrThrow(MediaStore.Images.ImageColumns.DATA));
					}
					
					finally{myCursor.close();}
					*/
					selectedPhoto = Uri.withAppendedPath(MediaStore.Images.Media.EXTERNAL_CONTENT_URI, String.valueOf(imageId));
					//Uri uriThumbnailImage = Uri.withAppendedPath(MediaStore.Images.Thumbnails.EXTERNAL_CONTENT_URI, String.valueOf(thumbnailImageId));
				}

				photoUri = selectedPhoto;
				ImageView imageView = (ImageView)findViewById(R.id.omThumb);
				//ContentResolver cr = getContentResolver();
				//cr.notifyChange(selectedPhoto, null);
				Bitmap bitmap;
				try {
					//bitmap = android.provider.MediaStore.Images.Media.getBitmap(cr, selectedPhoto);
					bitmap = (Bitmap)data.getExtras().get("data");
					imageView.setImageBitmap(bitmap);
					imageView.invalidate();
				} catch (Exception e) {
				}
				photoDone=true;
				if(recordingDone) {
					Button saveButton = (Button)findViewById(R.id.omSaveButton);
					saveButton.setVisibility(0);

				} 

			} else if (resultCode == RESULT_CANCELED) {
				// User canceled the image capture
			} else {
				// Image capture failed, advise user
			}
		} 
	} 

	private void sendMessages() {
		int dialogMax;
		final String allMessages[];

		cancelUpload = false;
		sending=true;

		final String bundledMessages=getPreference("log");
		if(bundledMessages != "" && bundledMessages != null) {
			String ret = doHTTPRequest(server + "/mobile/get_email_settings.php?id=" + phone_id);
			String retParts[] = ret.split(";");
			if(retParts.length==4) {
				final String email = retParts[0];
				final String pass = retParts[1];
				final String smtpServer = retParts[2];
				final String smtpPort = retParts[3];
				
				allMessages=bundledMessages.split("\\*");
				dialogMax=allMessages.length;

				dialog = new ProgressDialog(this);
				dialog.setCancelable(true);
				dialog.setMessage("Sending messages");
				dialog.setProgressStyle(ProgressDialog.STYLE_HORIZONTAL);
				dialog.setProgress(0);
				dialog.setMax(dialogMax);
				dialog.show();
				dialog.setOnCancelListener(new DialogInterface.OnCancelListener() {
					@Override
					public void onCancel(DialogInterface d) {
						cancelUpload=true;
						sending=false;
						upload.interrupt();
					}
				});

				upload = new Thread (new Runnable() {
					public void run() {
						for(int i=0;i<allMessages.length;i++) {
							if(!cancelUpload){
								String thisMessage=allMessages[i];
								if(!thisMessage.equals("") && thisMessage != null){
									String messageElements[] = thisMessage.split(";");
									Mail m = new Mail(email, pass, smtpServer, smtpPort);
									String[] toArr = {email};
									m.setTo(toArr); 
									m.setFrom(email);
									m.setSubject("ojovoz");
									m.setBody(messageElements[0]+";"+messageElements[1]+";"+messageElements[2]+";"+messageElements[3]+";"+messageElements[6]);
									boolean proceed=true;
									try {
										if(!messageElements[4].equals("null") && !messageElements[4].equals("")){
											File f1 = new File(messageElements[4]);
											if(f1.exists()){
												m.addAttachment(messageElements[4]);
											} else {
												proceed=false;
											}
										} else {
											proceed=false;
										}
										if(!messageElements[5].equals("null") && !messageElements[5].equals("")){
											File f2 = new File(messageElements[5]);
											if(f2.exists()){
												m.addAttachment(messageElements[5]);
											} else {
												proceed=false;
											}
										} else {
											proceed=false;
										}
									} catch(Exception e) {
										proceed=false;
									}
									if(proceed) {
										try {
											if(m.send()){
												updateMessages(messageElements[1]);
												deleteFileOM(messageElements[4]);
												deleteFileOM(messageElements[5]);
												//save messages minus current, delete files
											} else {
												cancelUpload=true;
												sending=false;
											}
										} catch(Exception e) {
											cancelUpload=true;
											sending=false;
										}
									} else {
										updateMessages(messageElements[1]);
										deleteFileOM(messageElements[4]);
										deleteFileOM(messageElements[5]);
										//save messages minus current
									}
								}
								progressHandler.sendMessage(progressHandler.obtainMessage());
							}
						}
					}
				});
				upload.start();
			} else {
				Toast.makeText(this, "Please connect to the Internet",Toast.LENGTH_SHORT).show();
				sending=false;
			}
		} else {
			Toast.makeText(this, "No messages to send",Toast.LENGTH_SHORT).show();
			sending=false;
		}
	}

	Handler progressHandler = new Handler() {
		public void handleMessage(Message msg) {
			dialog.incrementProgressBy(uploadIncrement);
			if(dialog.getProgress()==dialog.getMax()) {
				sending=false;
				dialog.dismiss();
				upload.interrupt();
			}
		}
	};

	private String doHTTPRequest(String urlRequest) {
		String ret="";
		if(isOnline()) {
			EasyHttpClient client = new EasyHttpClient();
			ret=client.get(urlRequest);
		} 
		return ret;
	}

	public boolean isOnline() {
		ConnectivityManager cm = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
		NetworkInfo netInfo = cm.getActiveNetworkInfo();
		if (netInfo != null && netInfo.isConnected()) {
			return true;
		}
		return false;
	}

	/*
    private void addExifMetadata(ExifInterface exif,String user,String date,String lat,String lon,String tag) {
    	if(lat!="-1.0"){
    		Double latVal = Double.parseDouble(lat);
    		boolean latNeg = latVal < 0;
    		latVal = Math.abs(latVal);
    		int latDeg = (int)Math.floor(latVal);
    		int latMin = (int)Math.floor((latVal*60)%60);
    		Double latSec = (latVal*3600)%60;
    		String latString = latDeg + "/1," + latMin + "/1," + latSec.toString() + "/1000";	
    		exif.setAttribute(ExifInterface.TAG_GPS_LATITUDE, latString);
    		if(latNeg){
    			exif.setAttribute(ExifInterface.TAG_GPS_LATITUDE_REF, "S");
    		} else {
    			exif.setAttribute(ExifInterface.TAG_GPS_LATITUDE_REF, "N");
    		}
    	}
    	if(lon!="-1.0"){
    		Double lonVal = Double.parseDouble(lon);
    		boolean lonNeg = lonVal < 0;
    		lonVal = Math.abs(lonVal);
    		int lonDeg = (int)Math.floor(lonVal);
    		int lonMin = (int)Math.floor((lonVal*60)%60);
    		Double lonSec = (lonVal*3600)%60;
    		String lonString = lonDeg + "/1," + lonMin + "/1," + lonSec.toString() + "/1000";	
    		exif.setAttribute(ExifInterface.TAG_GPS_LONGITUDE, lonString);
    		if(lonNeg){
    			exif.setAttribute(ExifInterface.TAG_GPS_LATITUDE_REF, "W");
    		} else {
    			exif.setAttribute(ExifInterface.TAG_GPS_LATITUDE_REF, "E");
    		}
    	}

    	String info=user+","+date+","+tag;
    	exif.setAttribute("IFD0_IMAGE_DESCRIPTION", info);

    	try {
    		exif.saveAttributes();
    	} catch (Exception e) {

    	}
    }
	 */

	private void updateMessages(String currentId) {
		String newMsg="";
		String msg=getPreference("log");
		String allMessages[]=msg.split("\\*");
		for(int i=0;i<allMessages.length;i++){
			String messageElements[] = allMessages[i].split(";");
			if(messageElements.length==7){
				if(!messageElements[1].equals(currentId)){
					if (newMsg.equals("")){
						newMsg=allMessages[i];
					} else {
						newMsg=newMsg+"*"+allMessages[i];
					}
				} 
			}
		}
		msg=newMsg;
		savePreference("log",msg,false);
	}

	private void deleteFileOM(String f) {
		File fileX = new File(f);
		fileX.delete();
	}

	private boolean isBetterLocation(Location loc){
		if (currentBestLocation == null) {
			// A new location is always better than no location
			return true;
		}

		// Check whether the new location fix is newer or older
		long timeDelta = loc.getTime() - currentBestLocation.getTime();
		boolean isSignificantlyNewer = timeDelta > TWO_MINUTES;
		boolean isSignificantlyOlder = timeDelta < -TWO_MINUTES;
		boolean isNewer = timeDelta > 0;

		// If it's been more than two minutes since the current location, use the new location
		// because the user has likely moved
		if (isSignificantlyNewer) {
			return true;
			// If the new location is more than two minutes older, it must be worse
		} else if (isSignificantlyOlder) {
			return false;
		}
		// Check whether the new location fix is more or less accurate
		int accuracyDelta = (int) (loc.getAccuracy() - currentBestLocation.getAccuracy());
		boolean isLessAccurate = accuracyDelta > 0;
		boolean isMoreAccurate = accuracyDelta < 0;
		boolean isSignificantlyLessAccurate = accuracyDelta > 200;

		// Check if the old and new location are from the same provider
		boolean isFromSameProvider = isSameProvider(loc.getProvider(),
				currentBestLocation.getProvider());

		// Determine location quality using a combination of timeliness and accuracy
		if (isMoreAccurate) {
			return true;
		} else if (isNewer && !isLessAccurate) {
			return true;
		} else if (isNewer && !isSignificantlyLessAccurate && isFromSameProvider) {
			return true;
		}
		return false;
	}

	private boolean isSameProvider(String provider1, String provider2) {
		if (provider1 == null) {
			return provider2 == null;
		}
		return provider1.equals(provider2);
	}

	public void resetPosition() {
		if(photoDone || recordingDone){
			
		} else {
			lat = -1;
			lon = -1;
			TextView textLocation = (TextView)findViewById(R.id.textLocation);
			textLocation.setText("Location unavailable");
			lastGPSFix = -1;
		}
	}

	private class OMLocationListener implements LocationListener {
		@Override
		public void onLocationChanged(Location loc) {
			if (loc != null) {
				if(isBetterLocation(loc)){
					lastGPSFix = Calendar.getInstance().getTimeInMillis();
					currentBestLocation=loc;
					lat = loc.getLatitude();
					lon = loc.getLongitude();
					TextView textLocation = (TextView)findViewById(R.id.textLocation);
					textLocation.setText(Double.toString(lat) + " , " + Double.toString(lon));
				} else {
					lastGPSFix = Calendar.getInstance().getTimeInMillis();
				}
			} else {
				lat = -1;
				lon = -1;
				TextView textLocation = (TextView)findViewById(R.id.textLocation);
				textLocation.setText("Location unavailable");
			}
		}

		@Override
		public void onProviderDisabled(String provider) {
			lat = -1;
			lon = -1;
			TextView textLocation = (TextView)findViewById(R.id.textLocation);
			textLocation.setText("Location unavailable");
		}

		@Override
		public void onProviderEnabled(String provider) {
			// TODO Auto-generated method stub
		}

		@Override
		public void onStatusChanged(String provider, int status, Bundle extras) {
			if (status==0) {
				lat = -1;
				lon = -1;
				TextView textLocation = (TextView)findViewById(R.id.textLocation);
				textLocation.setText("Location unavailable");
			}
		}
	} 
}