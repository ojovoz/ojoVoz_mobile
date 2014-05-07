package om;

import java.io.File;
import java.io.FileOutputStream;
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
import android.graphics.BitmapFactory;
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
	private int CAPTURE_IMAGE_ACTIVITY_REQUEST_CODE = 100;

	private String photoFile;
	private boolean photoDone = false;

	private AudioRecorder soundRecorder;
	private Boolean recording = false;
	private boolean recordingDone = false;

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
	
	@Override 
	public void onSaveInstanceState(Bundle outState) {
		outState.putString("photoFile", photoFile);
		outState.putString("tag", tag);
		outState.putBoolean("recordingDone", recordingDone);
		super.onSaveInstanceState(outState); 
	}
	
	@Override
	public void onRestoreInstanceState(Bundle savedInstanceState) {
		super.onRestoreInstanceState(savedInstanceState);
		photoFile = savedInstanceState.getString("photoFile"); 
		tag = savedInstanceState.getString("tag");
		recordingDone = savedInstanceState.getBoolean("recordingDone");
	}

	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.main);

		createDir(Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_PICTURES).getAbsolutePath() + File.separator + "ojovoz" + File.separator);
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

		user = getPreference("user");

		tag="";
		String tagList = "Elegir una palabra;" + getPreference("tags") + ";Otra palabra:";
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
		menu.add(0, 0, 0, "Enviar mensajes");
		menu.add(1, 1, 1, "Mi nombre");
		return true;
	}

	@Override
	public boolean onOptionsItemSelected(MenuItem item) {
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
						} else if(input.equals("tags")) {
							getTags();
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
				savePreference("id",input,false);
				return true;
			}
		};
		dlg.show();
	}

	public void createDir(String dir) {
		File folder = new File(dir);
		if(!folder.exists()){
			folder.mkdirs();
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

	public void showRecorder(View v) {
		if (!recording) {
			soundRecorder = new AudioRecorder();
			if (!soundRecorder.getFilename().equals("null")){
				deleteFileOM(soundRecorder.getFilename());
			}
			soundRecorder.modifyPath(Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_PICTURES).getAbsolutePath() + File.separator + "ojovoz" + File.separator+"s"+messageDate+".amr"); 
			try {
				Button buttonRecording = (Button)findViewById(R.id.omVoiceButton);
				buttonRecording.setText("Grabando voz ...");
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
					buttonRecording.setText("Grabar voz");
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
					textSoundRecorded.setText("Voz grabada");
				} catch (IOException e) {
					//
				}
			}
		}
	}
	
	private File createImageFile() throws IOException {
		String DATA_PATH = Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_PICTURES).getAbsolutePath() + File.separator + "ojovoz" + File.separator;
	    String timeStamp = new SimpleDateFormat("yyyyMMdd_HHmmss").format(new Date());
	    File image = new File(DATA_PATH + "i" + timeStamp + ".jpg");

	    photoFile = image.getAbsolutePath();
	    return image;
	}
	
	public void startCamera(View v) {
		showCamera();
	}

	private void showCamera() {
		Intent intent = new Intent(MediaStore.ACTION_IMAGE_CAPTURE);
		if(intent.resolveActivity(getPackageManager())!=null){
			File filename = null;
			try {
				filename = createImageFile();
			} catch (IOException ex) {
				
			}
			if (filename!=null){
				
				intent.putExtra(MediaStore.EXTRA_OUTPUT,Uri.fromFile(filename));
				startActivityForResult(intent, CAPTURE_IMAGE_ACTIVITY_REQUEST_CODE);
			}
		}
	}

	public void saveMessage(View v) {
		String writeText;
		String sUser;
		String sTag;
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
		photoFile = "";
		Button buttonRecording = (Button)findViewById(R.id.omVoiceButton);
		buttonRecording.setText("Grabar voz");
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
		Toast.makeText(this, "Mensaje guardado",Toast.LENGTH_SHORT).show();

	}

	public String getRealPathFromURI(Uri contentUri) {
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
	
	public boolean saveBitmap(Bitmap bitmap, String filename) {
		File f = new File(filename);
		
		try {
			
			f.createNewFile();
			FileOutputStream fo = new FileOutputStream(f);
			bitmap.compress(Bitmap.CompressFormat.JPEG, 100, fo);
			fo.flush();
			//fo.write(bytes.toByteArray());
			fo.close();
			
		} catch (IOException e) {
			Toast.makeText(this, "Error saving " + filename, Toast.LENGTH_SHORT).show();
			return false;
		}
		return true;
	}

	@Override
	protected void onActivityResult(int requestCode, int resultCode, Intent data) {
		super.onActivityResult(requestCode, resultCode, data);
		if(requestCode == CAPTURE_IMAGE_ACTIVITY_REQUEST_CODE && resultCode == RESULT_OK) {
			
			Bitmap thumb = BitmapFactory.decodeFile(photoFile); 
			if(thumb!=null){
				ImageView thumbnail = (ImageView)this.findViewById(R.id.omThumb);
				thumbnail.setImageBitmap(thumb);
				thumbnail.invalidate();
			}
			
			photoDone=true;
			if(recordingDone) {
				Button saveButton = (Button)findViewById(R.id.omSaveButton);
				saveButton.setVisibility(0);

			}
		}
	}
	
	public void clearDirectory(String dir) {
		File root = new File(dir);
		File[] Files = root.listFiles();
		if(Files != null) {
		    int j;
		    for(j = 0; j < Files.length; j++) {
		        Files[j].getAbsolutePath();
		        Files[j].delete();
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
				dialog.setMessage("Enviando mensajes");
				dialog.setProgressStyle(ProgressDialog.STYLE_HORIZONTAL);
				dialog.setProgress(0);
				dialog.setMax(dialogMax);
				dialog.show();
				dialog.setOnCancelListener(new DialogInterface.OnCancelListener() {
					@Override
					public void onCancel(DialogInterface d) {
						cancelUpload=true;
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
									}
								}
								progressHandler.sendMessage(progressHandler.obtainMessage());
							} else {
								sending=false;
								upload.interrupt();
							}
						}
						if(!cancelUpload) {
							clearDirectory(Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_PICTURES).getAbsolutePath() + File.separator + "ojovoz" + File.separator);
						}
					}
				});
				upload.start();
			} else {
				Toast.makeText(this, "Por favor conéctate a Internet",Toast.LENGTH_SHORT).show();
				sending=false;
			}
		} else {
			Toast.makeText(this, "No hay mensajes para enviar",Toast.LENGTH_SHORT).show();
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
			return true;
		}

		long timeDelta = loc.getTime() - currentBestLocation.getTime();
		boolean isSignificantlyNewer = timeDelta > TWO_MINUTES;
		boolean isSignificantlyOlder = timeDelta < -TWO_MINUTES;
		boolean isNewer = timeDelta > 0;

		if (isSignificantlyNewer) {
			return true;
		} else if (isSignificantlyOlder) {
			return false;
		}
		int accuracyDelta = (int) (loc.getAccuracy() - currentBestLocation.getAccuracy());
		boolean isLessAccurate = accuracyDelta > 0;
		boolean isMoreAccurate = accuracyDelta < 0;
		boolean isSignificantlyLessAccurate = accuracyDelta > 200;

		boolean isFromSameProvider = isSameProvider(loc.getProvider(),
				currentBestLocation.getProvider());
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
			textLocation.setText("Posición no disponible");
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
				textLocation.setText("Posición no disponible");
			}
		}

		@Override
		public void onProviderDisabled(String provider) {
			lat = -1;
			lon = -1;
			TextView textLocation = (TextView)findViewById(R.id.textLocation);
			textLocation.setText("Posición no disponible");
		}

		@Override
		public void onProviderEnabled(String provider) {
		}

		@Override
		public void onStatusChanged(String provider, int status, Bundle extras) {
			if (status==0) {
				lat = -1;
				lon = -1;
				TextView textLocation = (TextView)findViewById(R.id.textLocation);
				textLocation.setText("Posición no disponible");
			}
		}
	} 
}