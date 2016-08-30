package ojovoz;

import java.io.BufferedReader;
import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.concurrent.ExecutionException;

import ojovoz.skeleton.R;
import android.app.Activity;
import android.app.ProgressDialog;
import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.SharedPreferences;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.location.Location;
import android.location.LocationListener;
import android.location.LocationManager;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.Environment;
import android.os.Handler;
import android.os.Message;
import android.provider.MediaStore;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.Toast;
import android.graphics.Color;

public class mainActivity extends Activity {
	private int CAPTURE_IMAGE_ACTIVITY_REQUEST_CODE = 100;

	private String photoFile;
	private boolean photoDone = false;

	private AudioRecorder soundRecorder;
	private Boolean recording = false;
	private boolean recordingDone = false;

	private String messageDate;

	//private String tags[];
	private CharSequence tags[];
	protected ArrayList<CharSequence> selectedTags = new ArrayList<CharSequence>();
	private String tag;
	private String tagList;
	private Button tagSpinner;

	private String user="";
	private PromptDialog dlg = null;

	private String server="";
	private String phone_id="";

	private String mail="";
	private String pass="";
	private String smtpServer="";
	private String smtpPort="";

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
				if(mainActivity.this.lastGPSFix>0){
					long m = Calendar.getInstance().getTimeInMillis();
					if(Math.abs(mainActivity.this.lastGPSFix - m)>FIVE_MINUTES) {
						mainActivity.this.resetPosition();
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

		mail=getPreference("mail");
        pass=getPreference("pass");
        smtpServer=getPreference("smtpServer");
        smtpPort=getPreference("smtpPort");
        if (mail.equals("") || pass.equals("") || smtpServer.equals("") || smtpPort.equals("")){
            if(!server.equals("") && !phone_id.equals("")) {
                getEmailParams();
            }
        }

		tag="";
        String storedTags = getPreference("tags");
        if (storedTags.equals("") && !server.equals("") && !phone_id.equals("")){
            getTags(false);
        }
        tagList = storedTags + ";" + getBaseContext().getString(R.string.omNewTagText);
		tags = tagList.split(";");
		tagSpinner = (Button)findViewById(R.id.omTags);
		tagSpinner.setOnClickListener(new View.OnClickListener() {
			@Override
			public void onClick(View v) {
				switch (v.getId()) {
					case R.id.omTags:
						showSelectTagsDialog();
						break;
					default:
						break;
				}
			}
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

    public void getTags(boolean showMessage) {
        if(isOnline()) {
            new makeHTTPRequest().execute(server + "/mobile/get_tags.php?id=" + phone_id, "tags");
        } else {
            if(showMessage) {
                Toast.makeText(this, R.string.omPleaseConnectText, Toast.LENGTH_SHORT).show();
            }
        }
    }

    public void getEmailParams() {
        if(isOnline()){
            new makeHTTPRequest().execute(server + "/mobile/get_email_settings.php?id=" + phone_id, "mail");
        } else {
            mail="";
            savePreference("mail", "", false);
            pass="";
            savePreference("pass", "", false);
            smtpServer="";
            savePreference("smtpServer", "", false);
            smtpPort="";
            savePreference("smtpPort", "", false);
        }
    }

	public void showSelectTagsDialog() {
		boolean[] checkedTags = new boolean[tags.length];
		int count=tags.length;
		for(int i=0;i<count;i++) {
			checkedTags[i] = selectedTags.contains(tags[i]);
		}
		DialogInterface.OnMultiChoiceClickListener tagsDialogListener = new DialogInterface.OnMultiChoiceClickListener() {
			@Override
			public void onClick(DialogInterface dialog, int which, boolean isChecked) {
				if(isChecked) {
                    if(which==(tags.length-1)){
                        tag="*";
                        EditText newTag = (EditText)findViewById(R.id.omNewTag);
                        newTag.setVisibility(View.VISIBLE);
                    }
                    selectedTags.add(tags[which]);
				} else {
                    if(which==(tags.length-1)){
                        tag="";
                        EditText newTag = (EditText)findViewById(R.id.omNewTag);
                        newTag.setVisibility(View.INVISIBLE);
                    }
                    selectedTags.remove(tags[which]);
				}
				onChangeSelectedTags();
			}
		};
		AlertDialog.Builder builder = new AlertDialog.Builder(this);
		builder.setTitle(R.string.omChooseTagText);
		builder.setMultiChoiceItems(tags,checkedTags,tagsDialogListener);
        builder.setPositiveButton(R.string.omOKButtonText,new DialogInterface.OnClickListener() {
            public void onClick(DialogInterface dialog, int which) {
            } });
		//adding OK Cancel buttons http://stackoverflow.com/questions/15020878/i-want-to-show-ok-and-cancel-button-in-my-alert-dialog
		AlertDialog dialog = builder.create();
		dialog.show();
	}

	public void onChangeSelectedTags() {
		StringBuilder stringBuilder = new StringBuilder();
		for(CharSequence t : selectedTags) {
            if(!(t.toString().equals(getBaseContext().getString(R.string.omNewTagText)))) {
                stringBuilder.append(t + ",");
            }
		}
        if(stringBuilder.length()>0){
            stringBuilder.deleteCharAt(stringBuilder.length()-1);
            tagSpinner.setText(stringBuilder.toString());
        } else {
            tagSpinner.setText(R.string.omChooseTagText);
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
					deleteFileOM(messageElements[4],true);
					deleteFileOM(messageElements[5],false);
				}
			}
			savePreference("log","",false);
		}
	}

	@Override
	public boolean onCreateOptionsMenu(android.view.Menu menu) {
		super.onCreateOptionsMenu(menu);
		menu.add(0, 0, 0, R.string.omMenuOptSendText);
		menu.add(1, 1, 1, R.string.omMenuOptNameText);
		return true;
	}

	@Override
	public boolean onOptionsItemSelected(MenuItem item) {
		switch (item.getItemId()) {
		case 0:
			if(!sending && !recording){
                if(isOnline()){
                    sendMessages();
                } else {
                    Toast.makeText(this, R.string.omPleaseConnectText, Toast.LENGTH_SHORT).show();
                }
			}
			break;
		case 1:
			if(!sending && !recording){
				dlg = new PromptDialog(this, R.string.omDialogTitle, R.string.omTextFieldLabel, user) {
					@Override
					public boolean onOkClicked(String input) {
						if(input.equals("admin")){
							defineServer(mainActivity.this.server);
							definePhoneId(mainActivity.this.phone_id);
						} else if(input.equals("delete")) {
							deleteMessages();
						} else if(input.equals("tags")) {
							getTags(true);
						} else {
							mainActivity.this.user = input;
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
				mainActivity.this.server = input;
				savePreference("server",input,false);
                getEmailParams();
                getTags(false);
				return true;
			}
		};
		dlg.show();
	}

	public void definePhoneId(String current) {
		dlg = new PromptDialog(this, R.string.omDefineIdTitle, R.string.omIdLabel, current) {
			@Override
			public boolean onOkClicked(String input) {
				mainActivity.this.phone_id = input;
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
				deleteFileOM(soundRecorder.getFilename(),false);
			}
			soundRecorder.modifyPath(Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_PICTURES).getAbsolutePath() + File.separator + "ojovoz" + File.separator+"s"+messageDate+".amr");
			try {
				Button buttonRecording = (Button)findViewById(R.id.omVoiceButton);
				buttonRecording.setText(R.string.omSoundStr);
				buttonRecording.setTextColor(Color.RED);
				TextView textSoundRecorded = (TextView)findViewById(R.id.textSoundRecorded);
				textSoundRecorded.setText("");
				Button buttonSave = (Button)findViewById(R.id.omSaveButton);
                EditText newTag = (EditText)findViewById(R.id.omNewTag);
                newTag.setVisibility(View.INVISIBLE);
				buttonSave.setVisibility(View.INVISIBLE);
				tagSpinner.setVisibility(View.INVISIBLE);
				soundRecorder.start();
				recording = true;
			} catch (IOException e) {
				//
			}
		} else {
			if (soundRecorder != null) {
				try {
					Button buttonRecording = (Button)findViewById(R.id.omVoiceButton);
					buttonRecording.setText(R.string.omSoundButtonText);
					buttonRecording.setTextColor(Color.BLACK);
					soundRecorder.stop();
					recording = false;
					recordingDone=true;
					tagSpinner.setVisibility(View.VISIBLE);
                    if(tag=="*"){
                        EditText newTag = (EditText)findViewById(R.id.omNewTag);
                        newTag.setVisibility(View.VISIBLE);
                    }
					if(photoDone) {
						Button saveButton = (Button)findViewById(R.id.omSaveButton);
						saveButton.setVisibility(View.VISIBLE);
					}
					TextView textSoundRecorded = (TextView)findViewById(R.id.textSoundRecorded);
					textSoundRecorded.setText(R.string.omSoundFinishedStr);
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
		String sUser="";
		String sTag="";
		if (user.equals("") || user==null) {
			sUser="default";
		} else {
			sUser = user;
		}
		if (selectedTags.isEmpty()) {
			sTag="null";
		} else {
			StringBuilder stringBuilder = new StringBuilder();
			for(CharSequence t : selectedTags) {
				if(!(t.toString().equals(getBaseContext().getString(R.string.omNewTagText)))) {
					stringBuilder.append(t + ",");
				}
			}
			sTag=stringBuilder.toString();
			if(tag=="*") {
				EditText newTag = (EditText) findViewById(R.id.omNewTag);
				if (sTag.equals("")) {
					sTag = newTag.getText().toString();
				} else {
					sTag = sTag + "," + newTag.getText().toString();
				}
			}
			if (sTag.equals("")) {
				sTag = "null";
			}
		}
		messageDate=new SimpleDateFormat("dd_MM_yyyy_kk_mm_ss").format(new Date()).toString();
		writeText = sUser + ";" + messageDate + ";" + lat + ";" + lon + ";" + photoFile + ";" + soundRecorder.getFilename() + ";" + sTag + "*";
		savePreference("log",writeText,true);

		soundRecorder.clear();
		photoFile = "";
		Button buttonRecording = (Button)findViewById(R.id.omVoiceButton);
		buttonRecording.setText(R.string.omSoundButtonText);
		buttonRecording.setTextColor(Color.BLACK);
		TextView textRecording = (TextView)findViewById(R.id.textSoundRecorded);
		textRecording.setText(R.string.omEmpty);
		ImageView imageView = (ImageView)findViewById(R.id.omThumb);
		imageView.setImageResource(R.drawable.omBlank);
		Button buttonSave = (Button)findViewById(R.id.omSaveButton);
		buttonSave.setVisibility(View.INVISIBLE);
		EditText newTag = (EditText)findViewById(R.id.omNewTag);
		newTag.setVisibility(View.INVISIBLE);
		photoDone=false;
		recordingDone=false;
		tag="";
		selectedTags.clear();
        tagSpinner.setText(R.string.omChooseTagText);
		Toast.makeText(this, R.string.omMessageSavedText,Toast.LENGTH_SHORT).show();

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
				saveButton.setVisibility(View.VISIBLE);

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
        String dialogTitle = getBaseContext().getString(R.string.omSendingMessagesDialogText);

		cancelUpload = false;
		sending=true;

		final String bundledMessages=getPreference("log");
		if(bundledMessages != "" && bundledMessages != null) {
            String ret = null;
            if(mail.equals("") || pass.equals("") || smtpServer.equals("") || smtpPort.equals("")){
                try {
                    new makeHTTPRequest().execute(server + "/mobile/get_email_settings.php?id=" + phone_id,"mail").get();
                } catch (InterruptedException e) {
                    cancelUpload = true;
                } catch (ExecutionException e) {
                    cancelUpload = true;
                }
            }

			if(!mail.equals("") && !pass.equals("") && !smtpServer.equals("") && !smtpPort.equals("")) {

				allMessages=bundledMessages.split("\\*");
				dialogMax=allMessages.length;

				dialog = new ProgressDialog(this);
				dialog.setCancelable(true);
				dialog.setCanceledOnTouchOutside(false);
				dialog.setMessage(dialogTitle);
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
									Mail m = new Mail(mail, pass, smtpServer, smtpPort);
									String[] toArr = {mail};
									m.setTo(toArr);
									m.setFrom(mail);
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
												deleteFileOM(messageElements[4],true);
												deleteFileOM(messageElements[5],false);

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
										deleteFileOM(messageElements[4],true);
										deleteFileOM(messageElements[5],false);
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
				Toast.makeText(this, R.string.omPleaseConnectText,Toast.LENGTH_SHORT).show();
				sending=false;
			}
		} else {
			Toast.makeText(this, R.string.omNoMessagesText,Toast.LENGTH_SHORT).show();
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
					newMsg=newMsg+allMessages[i]+"*";
				}
			}
		}
		msg=newMsg;
		savePreference("log",msg,false);
	}

	private void deleteFileOM(String f, boolean isImage) {
		File fileX = new File(f);
        long imgFileDate = fileX.lastModified();
		fileX.delete();
		if(isImage) {
            String defaultGalleryPath = Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_DCIM).getAbsolutePath() + File.separator + "Camera";
            File imgs = new File(defaultGalleryPath);
            File imgsArray[] = imgs.listFiles();
            for (int i=0; i < imgsArray.length; i++) {
                if(Math.abs(imgsArray[i].lastModified() - imgFileDate) <= 3000){
                    imgsArray[i].delete();
                    break;
                }
            }
		}
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
			textLocation.setText(R.string.omLocationStr);
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
				textLocation.setText(R.string.omLocationStr);
			}
		}

		@Override
		public void onProviderDisabled(String provider) {
			lat = -1;
			lon = -1;
			TextView textLocation = (TextView)findViewById(R.id.textLocation);
			textLocation.setText(R.string.omLocationStr);
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
				textLocation.setText(R.string.omLocationStr);
			}
		}
	}

    private class makeHTTPRequest extends AsyncTask<String, Void, String> {

        private String pref;

        @Override
        protected String doInBackground(String... params) {

            pref = params[1];
            return getData(params[0]);

        }

        // onPostExecute displays the results of the AsyncTask.
        @Override
        protected void onPostExecute(String ret) {

            if (pref == "tags") {
                savePreference("tags", ret, false);
                tagList = ret + ";" + getBaseContext().getString(R.string.omNewTagText);
                tags = tagList.split(";");
                if(!ret.equals("")) {
                    Toast.makeText(mainActivity.this, R.string.omTagsDownloaded, Toast.LENGTH_SHORT).show();
                }
            } else if (pref == "mail") {
                String retParts[] = ret.split(";");
                if(retParts.length==4) {
                    mail = retParts[0];
                    savePreference("mail", mail, false);
                    pass = retParts[1];
                    savePreference("pass", pass, false);
                    smtpServer = retParts[2];
                    savePreference("smtpServer", smtpServer, false);
                    smtpPort = retParts[3];
                    savePreference("smtpPort", smtpPort, false);
                } else {
                    mail="";
                    savePreference("mail", "", false);
                    pass="";
                    savePreference("pass", "", false);
                    smtpServer="";
                    savePreference("smtpServer", "", false);
                    smtpPort="";
                    savePreference("smtpPort", "", false);
                }
            }
        }

        private String getData(String u) {
            String ret = null;
            URL url;
            HttpURLConnection urlConnection = null;
            try {
                url = new URL(u);
                urlConnection = (HttpURLConnection) url.openConnection();
                urlConnection.setRequestMethod("GET");
                urlConnection.setDoInput(true);
                urlConnection.connect();
                ret = readStream(urlConnection.getInputStream());
            } catch (Exception e) {
                ret="";
            } finally {
                if (urlConnection != null) {
                    urlConnection.disconnect();
                }
            }
            return ret;
        }

        private String readStream(InputStream in) throws IOException {

            BufferedReader r = null;
            r = new BufferedReader(new InputStreamReader(in));
            StringBuilder total = new StringBuilder();
            String line;
            while ((line = r.readLine()) != null) {
                total.append(line);
            }
            if (r != null) {
                r.close();
            }
            in.close();
            return total.toString();
        }
    }
}