package ojovoz.ojovoz;

import android.Manifest;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.location.Location;
import android.location.LocationListener;
import android.location.LocationManager;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.net.Uri;
import android.os.Build;
import android.os.Bundle;
import android.os.Environment;
import android.os.Handler;
import android.os.Message;
import android.provider.MediaStore;
import android.support.annotation.NonNull;
import android.support.v4.content.FileProvider;
import android.support.v7.app.AlertDialog;
import android.support.v7.app.AppCompatActivity;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewTreeObserver;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.FileWriter;
import java.io.IOException;
import java.io.InputStream;
import java.io.StringReader;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.Iterator;
import java.util.List;

import au.com.bytecode.opencsv.CSVReader;
import au.com.bytecode.opencsv.CSVWriter;

/**
 * Created by Eugenio on 18/02/2019.
 */
public class pictureSound extends AppCompatActivity implements httpConnection.AsyncResponse {

    private int CAPTURE_IMAGE_ACTIVITY_REQUEST_CODE = 100;
    private int CAMERA_PERMISSION = 232323;
    private int AUDIO_PERMISSION = 23232323;
    private int LOCATION_PERMISSION = 2323;

    String photoFile = "";
    String prevPhotoFile = "";
    boolean photoDone;

    private audioRecorder soundRecorder;
    private Boolean recording;
    private boolean recordingDone;
    String soundFile = "";
    String prevSoundFile = "";

    Date messageDate;

    ArrayList<String> filesToDelete;

    public String user;
    public String server;
    public String phoneID;
    String ojoVozEmail = "";
    String ojoVozPass = "";
    String multimediaSubject = "";
    String smtpServer = "";
    String smtpPort = "";
    ProgressDialog downloadingParamsDialog;
    boolean bConnecting;
    private ProgressDialog sendingMultimediaDialog;
    private Thread uploadMultimedia;
    ArrayList<oLog> logList;
    private int[] multimediaCleanUpList;
    private ArrayList<String> deleteFiles = new ArrayList<>();
    private Context context;

    boolean bChanges = false;

    int displayWidth;
    int displayHeight;

    preferenceManager preferences;

    private LocationManager lm;
    private LocationListener locationListener;
    private Location currentBestLocation = null;
    private double lat = -1;
    private double lon = -1;
    private static final int TWO_MINUTES = 1000 * 60 * 2;
    private static final int FIVE_MINUTES = 1000 * 60 * 5;
    private long lastGPSFix = -1;
    private locationTimer gpsTimer;

    ArrayList<oTag> tagList;
    ArrayList<oTag> selectedTags;
    public CharSequence tagNamesArray[];
    public String otherTags = "";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_picture_sound);

        createDir(Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_PICTURES).getAbsolutePath() + File.separator + getString(R.string.app_name) + File.separator);

        context = this;

        preferences = new preferenceManager(this);
        user = preferences.getPreference("user");
        server = preferences.getPreference("server");
        phoneID = preferences.getPreference("phoneID");
        if (server.isEmpty() || phoneID.isEmpty() || user.isEmpty()) {
            goToSettings();
        } else {

            TextView tt = (TextView) findViewById(R.id.latLong);
            tt.setText(R.string.noLatLong);

            photoDone = false;
            recording = false;
            recordingDone = false;
            filesToDelete = new ArrayList<>();
            messageDate = new Date();

            final LinearLayout root = (LinearLayout) findViewById(R.id.mainRoot);
            ViewTreeObserver o = root.getViewTreeObserver();
            o.addOnGlobalLayoutListener(new ViewTreeObserver.OnGlobalLayoutListener() {
                @Override
                public void onGlobalLayout() {

                    displayWidth = root.getWidth();
                    displayHeight = root.getHeight();

                    if (displayHeight > 0) {
                        ImageView i = (ImageView) findViewById(R.id.thumbnail);
                        int w = (displayWidth * 400) / 540;
                        i.getLayoutParams().width = w;
                        i.getLayoutParams().height = (int) (w / 1.5);
                        i.requestLayout();

                        root.getViewTreeObserver().removeOnGlobalLayoutListener(this);

                        checkPermissions();
                        createTagList();

                        if (user.isEmpty()) {
                            modifyMyName();
                        }
                    }
                }
            });
        }
    }

    @Override
    public void onBackPressed() {
        tryExit(0);
    }

    @Override
    public boolean onCreateOptionsMenu(android.view.Menu menu) {
        super.onCreateOptionsMenu(menu);
        menu.add(0, 0, 0, R.string.opMyName);
        menu.add(1, 1, 1, R.string.opMessages);
        menu.add(2, 2, 2, R.string.opSendAllMessages);
        menu.add(3, 3, 3, R.string.opGoToWeb);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()) {
            case 0:
                modifyMyName();
                break;
            case 1:
                tryExit(1);
                break;
            case 2:
                sendMessages();
                break;
            case 3:
                goToWebPage();
                break;
        }
        return super.onOptionsItemSelected(item);
    }

    public void tryExit(int action) {
        if (bChanges) {
            confirmExit(action);
        } else {
            switch (action) {
                case 0:
                    finish();
                    break;
                case 1:
                    goToMessages();
                    break;
            }
        }
    }

    public void confirmExit(int action) {
        final int a = action;
        AlertDialog.Builder exitDialog = new AlertDialog.Builder(this);
        exitDialog.setMessage(R.string.pictureSoundNotSavedText);
        exitDialog.setNegativeButton(R.string.noButtonText, null);
        exitDialog.setPositiveButton(R.string.yesButtonText, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialogInterface, int i) {
                if (!photoFile.isEmpty()) {
                    filesToDelete.add(photoFile);
                }
                if (!soundFile.isEmpty()) {
                    filesToDelete.add(soundFile);
                }
                if (filesToDelete.size() > 0) {
                    deleteFiles();
                }
                switch (a) {
                    case 0:
                        finish();
                        break;
                    case 1:
                        goToMessages();
                        break;
                }

            }
        });
        exitDialog.create();
        exitDialog.show();
    }

    public void checkPermissions() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            if (checkSelfPermission(Manifest.permission.ACCESS_FINE_LOCATION) == PackageManager.PERMISSION_GRANTED &&
                    checkSelfPermission(Manifest.permission.ACCESS_COARSE_LOCATION) == PackageManager.PERMISSION_GRANTED) {
                startGPS();
            } else {
                String[] permissionRequest = {Manifest.permission.ACCESS_FINE_LOCATION, Manifest.permission.ACCESS_COARSE_LOCATION};
                requestPermissions(permissionRequest, LOCATION_PERMISSION);
            }
        } else {
            startGPS();
        }
    }

    public void createTagList() {
        oTag t = new oTag(this);
        tagList = t.sortTags(t.createTagList(), false, -1);
        t.tag = getString(R.string.otherWord);
        t.line = -1;
        tagList.add(t);

        tagNamesArray = t.getTagNames(tagList).toArray(new CharSequence[tagList.size()]);

        selectedTags = new ArrayList<>();
    }

    public void displayTags(View v) {
        boolean[] checkedTags = new boolean[tagNamesArray.length];
        for (int i = 0; i < tagNamesArray.length; i++) {
            Iterator<oTag> iterator = selectedTags.iterator();
            while (iterator.hasNext()) {
                oTag t = iterator.next();
                if (t.tag.equals(tagNamesArray[i])) {
                    checkedTags[i] = true;
                    break;
                }
            }
        }

        DialogInterface.OnMultiChoiceClickListener tagsDialogListener = new DialogInterface.OnMultiChoiceClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which, boolean isChecked) {
                if (isChecked) {
                    oTag t = tagList.get(which);
                    if (t.line == -1) {
                        toggleNewTagVisibility();
                    } else {
                        selectedTags.add(tagList.get(which));
                    }
                } else {
                    oTag removeTag = tagList.get(which);
                    if (removeTag.line == -1) {
                        toggleNewTagVisibility();
                    } else {
                        Iterator<oTag> iterator = selectedTags.iterator();
                        int index = 0;
                        while (iterator.hasNext()) {
                            oTag t = iterator.next();
                            if (t.tag == removeTag.tag) {
                                break;
                            }
                            index++;
                        }
                        selectedTags.remove(index);
                    }
                }
            }
        };
        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setTitle(R.string.selectTagsTitle);
        builder.setMultiChoiceItems(tagNamesArray, checkedTags, tagsDialogListener);
        builder.setPositiveButton(R.string.okButtonText, new DialogInterface.OnClickListener() {
            public void onClick(DialogInterface dialog, int which) {
                Button bt = (Button)findViewById(R.id.tagsButton);
                String st = getTagsString(false);
                if(!st.isEmpty()) {
                    if (st.length() > 16) {
                        st = st.substring(0, 15) + "...";
                    }
                    bt.setText(st);
                }
            }
        });
        AlertDialog dialog = builder.create();
        dialog.show();
    }

    public String getTagsString(boolean includeOther) {
        String ret = "";
        Iterator<oTag> iterator = selectedTags.iterator();
        while (iterator.hasNext()) {
            oTag t = iterator.next();
            ret = (ret.isEmpty()) ? t.tag : ret + "," + t.tag;
        }

        if(includeOther) {
            EditText tt = (EditText) findViewById(R.id.newTag);
            if (tt.getVisibility() == View.VISIBLE && !tt.getText().toString().isEmpty()) {
                otherTags = tt.getText().toString();
                otherTags.replaceAll("\\;", ",");
                ret = (ret.isEmpty()) ? otherTags.trim() : ret + "," + otherTags.trim();
            }
        }

        return ret;
    }

    public void toggleNewTagVisibility(){
        EditText tt = (EditText)findViewById(R.id.newTag);
        if(tt.getVisibility()==View.VISIBLE){
            tt.setVisibility(View.GONE);
        } else {
            tt.setVisibility(View.VISIBLE);
        }
    }

    public void goToMessages() {
        final Context context = this;
        Intent i = new Intent(context, messages.class);
        i.putExtra("displayWidth", displayWidth);
        i.putExtra("displayHeight", displayHeight);
        startActivity(i);
        finish();
    }

    public void goToSettings() {
        final Context context = this;
        Intent i = new Intent(context, settings.class);
        startActivity(i);
        finish();
    }

    public void goToWebPage() {
        if (isOnline()) {
            String webpage = server;
            Intent i = new Intent(Intent.ACTION_VIEW);
            if (i.resolveActivity(getPackageManager()) != null) {
                i.setData(Uri.parse(webpage));
                startActivity(i);
            }
        } else {
            Toast.makeText(this, R.string.pleaseConnectMessage, Toast.LENGTH_SHORT).show();
        }
    }

    public boolean isOnline() {
        ConnectivityManager cm = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
        NetworkInfo netInfo = cm.getActiveNetworkInfo();
        return netInfo != null && netInfo.isConnected();
    }

    public void modifyMyName() {
        promptDialog dlg = new promptDialog(this, R.string.emptyString, R.string.defineMyNameLabel, user) {
            @Override
            public boolean onOkClicked(String input) {
                if (input.equals("admin")) {
                    goToSettings();
                } else {
                    pictureSound.this.user = input;
                    preferences.savePreference("user", input);
                }
                return true;
            }
        };
        dlg.show();
    }

    public void createDir(String dir) {
        File folder = new File(dir);
        if (!folder.exists()) {
            folder.mkdirs();
        }
    }

    public void startCamera(View v) {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            if (checkSelfPermission(android.Manifest.permission.CAMERA) == PackageManager.PERMISSION_GRANTED) {
                showCamera();
            } else {
                String[] permissionRequest = {android.Manifest.permission.CAMERA, android.Manifest.permission.WRITE_EXTERNAL_STORAGE};
                requestPermissions(permissionRequest, CAMERA_PERMISSION);
            }
        } else {
            showCamera();
        }
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull String[] permissions, @NonNull int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        if (requestCode == CAMERA_PERMISSION) {
            if (grantResults[0] == PackageManager.PERMISSION_GRANTED && grantResults[1] == PackageManager.PERMISSION_GRANTED) {
                showCamera();
            }
        } else if (requestCode == AUDIO_PERMISSION) {
            if (grantResults[0] == PackageManager.PERMISSION_GRANTED && grantResults[1] == PackageManager.PERMISSION_GRANTED) {
                doRecordSound();
            }
        } else if (requestCode == LOCATION_PERMISSION) {
            if (grantResults[0] == PackageManager.PERMISSION_GRANTED && grantResults[1] == PackageManager.PERMISSION_GRANTED) {
                startGPS();
            }
        }
    }

    private void showCamera() {
        Intent intent = new Intent(MediaStore.ACTION_IMAGE_CAPTURE);
        if (intent.resolveActivity(getPackageManager()) != null) {
            File filename = null;
            try {
                filename = createImageFile();
            } catch (IOException ex) {

            }
            if (filename != null) {
                if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
                    intent.putExtra(MediaStore.EXTRA_OUTPUT, FileProvider.getUriForFile(pictureSound.this, BuildConfig.APPLICATION_ID + ".provider", filename));
                } else {
                    intent.putExtra(MediaStore.EXTRA_OUTPUT, Uri.fromFile(filename));
                }
                startActivityForResult(intent, CAPTURE_IMAGE_ACTIVITY_REQUEST_CODE);
            }
        }
    }

    private File createImageFile() throws IOException {
        String dataPath = Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_PICTURES).getAbsolutePath() + File.separator + getString(R.string.app_name) + File.separator;
        String timeStamp = new SimpleDateFormat("yyyyMMdd_HHmmss").format(new Date());
        File image = new File(dataPath + "i" + timeStamp + ".jpg");

        photoFile = image.getAbsolutePath();
        return image;
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == CAPTURE_IMAGE_ACTIVITY_REQUEST_CODE && resultCode == RESULT_OK) {
            Bitmap thumb = scaleBitmap(photoFile);
            if (thumb != null) {
                ImageView thumbnail = (ImageView) this.findViewById(R.id.thumbnail);
                thumbnail.setImageBitmap(thumb);
                thumbnail.invalidate();
            }

            photoDone = true;
            bChanges = true;
            if (!prevPhotoFile.isEmpty()) {
                filesToDelete.add(prevPhotoFile);
            }
            prevPhotoFile = photoFile;

            if (recordingDone) {
                Button saveButton = (Button) findViewById(R.id.saveButton);
                saveButton.setVisibility(View.VISIBLE);

            }

        } else if (!prevPhotoFile.isEmpty()) {
            photoFile = prevPhotoFile;
        }
    }

    public Bitmap scaleBitmap(String path) {
        Bitmap ret = null;
        final int IMAGE_MAX_SIZE = 1200000;
        try {
            InputStream in = null;
            in = new FileInputStream(path);
            BitmapFactory.Options options = new BitmapFactory.Options();
            options.inJustDecodeBounds = true;
            BitmapFactory.decodeStream(in, null, options);
            in.close();

            int scale = 1;
            while ((options.outWidth * options.outHeight) * (1 / Math.pow(scale, 2)) > IMAGE_MAX_SIZE) {
                scale++;
            }

            in = new FileInputStream(path);

            if (scale > 1) {
                scale--;
                options = new BitmapFactory.Options();
                options.inSampleSize = scale;
                ret = BitmapFactory.decodeStream(in, null, options);
                in.close();

                int height = ret.getHeight();
                int width = ret.getWidth();

                double y = Math.sqrt(IMAGE_MAX_SIZE / (((double) width) / height));
                double x = (y / height) * width;

                Bitmap scaledBitmap = Bitmap.createScaledBitmap(ret, (int) x, (int) y, true);

                try {
                    FileOutputStream out = new FileOutputStream(path);
                    scaledBitmap.compress(Bitmap.CompressFormat.JPEG, 80, out);
                    out.close();
                } catch (Exception e) {

                }

                ret.recycle();
                ret = scaledBitmap;
                return ret;

            } else {
                ret = BitmapFactory.decodeStream(in);
                in.close();
                return ret;
            }


        } catch (IOException e) {

        }
        return ret;
    }

    public void recordSound(View v) {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.M) {
            if (checkSelfPermission(android.Manifest.permission.RECORD_AUDIO) == PackageManager.PERMISSION_GRANTED) {
                doRecordSound();
            } else {
                String[] permissionRequest = {android.Manifest.permission.RECORD_AUDIO, android.Manifest.permission.WRITE_EXTERNAL_STORAGE};
                requestPermissions(permissionRequest, AUDIO_PERMISSION);
            }
        } else {
            doRecordSound();
        }
    }

    private void doRecordSound() {
        if (!recording) {
            soundRecorder = new audioRecorder();
            if (!soundRecorder.getFilename().equals("null")) {
                deleteFile(soundRecorder.getFilename(), false);
            }
            String timeStamp = new SimpleDateFormat("yyyyMMdd_HHmmss").format(new Date());
            soundFile = Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_PICTURES).getAbsolutePath() + File.separator + getString(R.string.app_name) + File.separator + "s" + timeStamp + ".amr";
            soundRecorder.modifyPath(soundFile);
            try {
                Button bRec = (Button) findViewById(R.id.soundButton);
                bRec.setBackgroundResource(R.drawable.button_background_rec);
                bRec.setText(R.string.soundButtonRecordingLabel);
                Button bSave = (Button) findViewById(R.id.saveButton);
                bSave.setVisibility(View.GONE);
                soundRecorder.start();
                recording = true;
            } catch (IOException e) {
                //
            }
        } else {
            if (soundRecorder != null) {
                try {
                    Button bRec = (Button) findViewById(R.id.soundButton);
                    bRec.setText(R.string.soundButtonLabelAgain);
                    bRec.setBackgroundResource(R.drawable.button_background);
                    soundRecorder.stop();
                    recording = false;
                    recordingDone = true;
                    bChanges = true;
                    if (!prevSoundFile.isEmpty()) {
                        filesToDelete.add(prevSoundFile);
                    }
                    prevSoundFile = soundFile;
                    if (photoDone) {
                        Button saveButton = (Button) findViewById(R.id.saveButton);
                        saveButton.setVisibility(View.VISIBLE);
                    }
                } catch (IOException e) {
                    //
                }
            }
        }
    }

    public void deleteFiles() {
        Iterator<String> iterator = filesToDelete.iterator();
        while (iterator.hasNext()) {
            String f = iterator.next();
            deleteFile(f, f.contains(".jpg"));
        }
    }

    public void saveMessage(View v) {
        if (filesToDelete.size() > 0) {
            deleteFiles();
        }

        String tags = getTagsString(true);
        messageDate = new Date();

        oLog log = new oLog(this);
        log.appendToLog(user, messageDate, tags, Double.toString(lat), Double.toString(lon), photoFile, soundFile);

        Button bs = (Button) findViewById(R.id.soundButton);
        bs.setText(R.string.soundButtonLabel);
        ImageView it = (ImageView) findViewById(R.id.thumbnail);
        it.setImageResource(R.drawable.blank_image);
        bs = (Button) findViewById(R.id.saveButton);
        bs.setVisibility(View.GONE);
        Button bt = (Button)findViewById(R.id.tagsButton);
        bt.setText(R.string.tagsButtonLabel);

        filesToDelete = new ArrayList<>();

        photoDone = false;
        photoFile = "";
        prevPhotoFile = "";

        soundRecorder.clear();
        recordingDone = false;
        soundFile = "";
        prevSoundFile = "";

        bChanges = false;

        if (!otherTags.isEmpty()) {
            oTag t = new oTag(this);
            t.appendTag(tagList, otherTags);
        }
        selectedTags = new ArrayList<>();
        otherTags = "";
        EditText tt = (EditText)findViewById(R.id.newTag);
        tt.setVisibility(View.GONE);

        createTagList();

        Toast.makeText(this, R.string.pictureSoundSavedMessage, Toast.LENGTH_SHORT).show();
    }

    private void deleteFile(String f, boolean isImage) {
        File fileX = new File(f);
        long imgFileDate = fileX.lastModified();
        fileX.delete();
        if (isImage) {
            String defaultGalleryPath = Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_DCIM).getAbsolutePath() + File.separator + "Camera";
            File imgs = new File(defaultGalleryPath);
            File imgsArray[] = imgs.listFiles();
            for (int i = 0; i < imgsArray.length; i++) {
                if (Math.abs(imgsArray[i].lastModified() - imgFileDate) <= 3000) {
                    imgsArray[i].delete();
                    break;
                }
            }
        }
    }

    public void createLogList() {
        oLog log = new oLog(this);
        logList = log.sortLogByDate(log.createLog(), true, -1);
    }

    public boolean getEmailParams() {
        boolean ret = false;
        csvFileManager paramList;

        paramList = new csvFileManager("parameters");
        List<String[]> paramCSV = paramList.read(this);
        if (paramCSV != null) {
            Iterator<String[]> iterator = paramCSV.iterator();
            while (iterator.hasNext()) {
                String[] record = iterator.next();
                if (record.length == 5) {
                    ojoVozEmail = record[0];
                    ojoVozPass = record[1];
                    multimediaSubject = record[2];
                    smtpServer = record[3];
                    smtpPort = record[4];
                    ret = true;
                }
            }
        }
        return ret;
    }

    public void sendMessages() {
        httpConnection http = new httpConnection(this, this);
        if (http.isOnline()) {
            bConnecting=true;
            if (getEmailParams()) {
                doSendMultimediaMessages();
            } else {
                CharSequence dialogTitle = getString(R.string.downloadingParametersMessage);
                downloadingParamsDialog = new ProgressDialog(this);
                downloadingParamsDialog.setCancelable(true);
                downloadingParamsDialog.setCanceledOnTouchOutside(false);
                downloadingParamsDialog.setMessage(dialogTitle);
                downloadingParamsDialog.setIndeterminate(true);
                downloadingParamsDialog.show();
                downloadingParamsDialog.setOnCancelListener(new DialogInterface.OnCancelListener() {
                    @Override
                    public void onCancel(DialogInterface d) {
                        bConnecting = false;
                        downloadingParamsDialog.dismiss();
                    }
                });
                http.execute(server + "/mobile/get_parameters.php?id=" + phoneID, "");
            }

        } else {
            Toast.makeText(this, R.string.pleaseConnectMessage, Toast.LENGTH_SHORT).show();
            bConnecting = false;
        }
    }

    public void doSendMultimediaMessages() {

        ArrayList<String> b = new ArrayList<>();
        final ArrayList<oLog> attachments = new ArrayList<>();
        createLogList();
        Iterator<oLog> iterator = logList.iterator();

        while (iterator.hasNext()) {
            oLog l = iterator.next();
            b.add(l.toString(";"));
            attachments.add(l);
        }

        if (!b.isEmpty()) {
            final ArrayList<String> emailBody = b;
            multimediaCleanUpList = new int[attachments.size()];
            httpConnection http = new httpConnection(this, this);
            if (http.isOnline()) {
                sendingMultimediaDialog = new ProgressDialog(this);
                sendingMultimediaDialog.setCancelable(true);
                sendingMultimediaDialog.setCanceledOnTouchOutside(false);
                CharSequence dialogTitle = getString(R.string.sendingMultimediaRecordsMessage);
                sendingMultimediaDialog.setMessage(dialogTitle);
                sendingMultimediaDialog.setProgressStyle(ProgressDialog.STYLE_HORIZONTAL);
                sendingMultimediaDialog.setProgress(0);
                int dialogMax = attachments.size();
                sendingMultimediaDialog.setMax(dialogMax);
                sendingMultimediaDialog.setOnCancelListener(new DialogInterface.OnCancelListener() {
                    @Override
                    public void onCancel(DialogInterface d) {
                        bConnecting = false;
                        uploadMultimedia.interrupt();
                        sendingMultimediaDialog.dismiss();
                    }
                });
                sendingMultimediaDialog.show();

                uploadMultimedia = new Thread(new Runnable() {
                    public void run() {

                        int n = 0;
                        Iterator<oLog> iterator = attachments.iterator();
                        while (iterator.hasNext() && bConnecting) {
                            oLog item = iterator.next();
                            String body = emailBody.get(n);

                            Mail m = new Mail(ojoVozEmail, ojoVozPass, smtpServer, smtpPort);
                            String[] toArr = {ojoVozEmail};
                            m.setTo(toArr);
                            m.setFrom(ojoVozEmail);
                            m.setSubject("ojovoz");
                            m.setBody(body);
                            boolean proceed = true;

                            try {
                                File f1 = new File(item.pictureFile);
                                if (f1.exists()) {
                                    m.addAttachment(item.pictureFile);
                                } else {
                                    proceed = false;
                                }
                                File f2 = new File(item.soundFile);
                                if (f2.exists()) {
                                    m.addAttachment(item.soundFile);
                                } else {
                                    proceed = false;
                                }
                            } catch (Exception e) {
                                proceed = false;
                            }

                            if (proceed) {
                                try {
                                    if (m.send()) {
                                        multimediaCleanUpList[n] = item.line;
                                        deleteFiles.add(item.pictureFile);
                                        deleteFiles.add(item.soundFile);
                                    }
                                } catch (Exception e) {

                                }
                            }
                            progressHandler.sendMessage(progressHandler.obtainMessage());

                            n++;

                        }
                    }
                });
                uploadMultimedia.start();

            } else {
                Toast.makeText(this, R.string.pleaseConnectMessage, Toast.LENGTH_SHORT).show();
                bConnecting = false;
            }
        } else {
            Toast.makeText(this, R.string.noMessages, Toast.LENGTH_SHORT).show();
            bConnecting = false;
        }
    }

    Handler progressHandler = new Handler() {
        @Override
        public void handleMessage(Message msg) {
            sendingMultimediaDialog.incrementProgressBy(1);
            if (sendingMultimediaDialog.getProgress() == sendingMultimediaDialog.getMax()) {
                bConnecting = false;
                sendingMultimediaDialog.dismiss();
                uploadMultimedia.interrupt();

                oLog l = new oLog(context);
                l.deleteLogItems(multimediaCleanUpList);

                deleteImgSndFiles(deleteFiles);
            }
        }
    };

    public void deleteImgSndFiles(ArrayList<String> deleteFiles) {
        Iterator<String> iterator = deleteFiles.iterator();
        while (iterator.hasNext()) {
            String f = iterator.next();
            File fileX = new File(f);
            long imgFileDate = fileX.lastModified();
            fileX.delete();
            if (f.contains("jpg")) {
                String defaultGalleryPath = Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_DCIM).getAbsolutePath() + File.separator + "Camera";
                File imgs = new File(defaultGalleryPath);
                File imgsArray[] = imgs.listFiles();
                for (int i = 0; i < imgsArray.length; i++) {
                    if (Math.abs(imgsArray[i].lastModified() - imgFileDate) <= 3000) {
                        imgsArray[i].delete();
                        break;
                    }
                }
            }
        }
    }

    public void startGPS() {
        lm = (LocationManager) getSystemService(Context.LOCATION_SERVICE);
        locationListener = new OMLocationListener();
        try {
            lm.requestLocationUpdates(LocationManager.GPS_PROVIDER, 5000, 5, locationListener);
            gpsTimer = new locationTimer(10000, new Runnable() {
                public void run() {
                    if (pictureSound.this.lastGPSFix > 0) {
                        long m = Calendar.getInstance().getTimeInMillis();
                        if (Math.abs(pictureSound.this.lastGPSFix - m) > FIVE_MINUTES) {
                            pictureSound.this.resetPosition();
                        }
                    }
                }
            });
            gpsTimer.start();
        } catch (SecurityException e) {

        }
    }

    private boolean isBetterLocation(Location loc) {
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
        if (photoDone || recordingDone) {

        } else {
            lat = -1;
            lon = -1;
            TextView textLocation = (TextView) findViewById(R.id.latLong);
            textLocation.setText(R.string.noLatLong);
            lastGPSFix = -1;
        }
    }

    private class OMLocationListener implements LocationListener {
        @Override
        public void onLocationChanged(Location loc) {
            if (loc != null) {
                if (isBetterLocation(loc)) {
                    lastGPSFix = Calendar.getInstance().getTimeInMillis();
                    currentBestLocation = loc;
                    lat = loc.getLatitude();
                    lon = loc.getLongitude();
                    TextView textLocation = (TextView) findViewById(R.id.latLong);
                    textLocation.setText(Double.toString(lat) + " , " + Double.toString(lon));
                } else {
                    lastGPSFix = Calendar.getInstance().getTimeInMillis();
                }
            } else {
                lat = -1;
                lon = -1;
                TextView textLocation = (TextView) findViewById(R.id.latLong);
                textLocation.setText(R.string.noLatLong);
            }
        }

        @Override
        public void onProviderDisabled(String provider) {
            lat = -1;
            lon = -1;
            TextView textLocation = (TextView) findViewById(R.id.latLong);
            textLocation.setText(R.string.noLatLong);
        }

        @Override
        public void onProviderEnabled(String provider) {
        }

        @Override
        public void onStatusChanged(String provider, int status, Bundle extras) {
            if (status == 0) {
                lat = -1;
                lon = -1;
                TextView textLocation = (TextView) findViewById(R.id.latLong);
                textLocation.setText(R.string.noLatLong);
            }
        }
    }

    @Override
    public void processFinish(String output) {

        downloadingParamsDialog.dismiss();
        String[] nextLine;
        CSVReader reader = new CSVReader(new StringReader(output), ',', '"');
        File file = new File(this.getFilesDir(), "parameters");
        try {
            FileWriter w = new FileWriter(file);
            CSVWriter writer = new CSVWriter(w, ',', '"');
            while ((nextLine = reader.readNext()) != null) {
                writer.writeNext(nextLine);
            }
            writer.close();
            reader.close();
            if (getEmailParams()) {
                doSendMultimediaMessages();
            } else {
                Toast.makeText(this, R.string.incorrectInternetParamsMessage, Toast.LENGTH_SHORT).show();
                bConnecting = false;
            }
        } catch (IOException e) {
            bConnecting = false;
        }

    }

}


