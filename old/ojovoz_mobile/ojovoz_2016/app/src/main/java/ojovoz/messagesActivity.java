package ojovoz;

import android.app.Activity;
import android.app.AlertDialog;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.SharedPreferences;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.Color;
import android.media.MediaPlayer;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.Environment;
import android.os.Handler;
import android.os.Message;
import android.util.TypedValue;
import android.view.Gravity;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.ImageView;
import android.widget.TableLayout;
import android.widget.TableRow;
import android.widget.TextView;
import android.widget.Toast;

import java.io.BufferedReader;
import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.Vector;
import java.util.concurrent.ExecutionException;

import ojovoz.skeleton.R;


/**
 * Created by Eugenio on 19/07/2016.
 */
public class messagesActivity extends Activity {

    Vector messages = new Vector();
    Vector selectedMessages = new Vector();
    int maxMessages = 10;
    int from = 0;

    Vector checkBoxes = new Vector();

    ImageView lastPlayed = null;
    int lastPlayedId;
    MediaPlayer sndPlayer = new MediaPlayer();
    boolean playing = false;

    private ProgressDialog dialog;
    private int uploadIncrement = 1;
    private Thread upload;
    private boolean cancelUpload;
    private boolean sending = false;

    private String server = "";
    private String phone_id = "";
    private PromptDialog dlg = null;

    String mail = "";
    String pass = "";
    String smtpServer = "";
    String smtpPort = "";

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.messages);

        //Toast.makeText(this, R.string.omPleaseWait, Toast.LENGTH_SHORT).show();

        server = getPreference("server");
        phone_id = getPreference("id");
        mail = getPreference("mail");
        pass = getPreference("pass");
        smtpServer = getPreference("smtpServer");
        smtpPort = getPreference("smtpPort");

        //populateTableLayout();

        new Handler().postDelayed(new Runnable() {

            @Override
            public void run() {
                new Thread() {
                    @Override
                    public void run() {
                        populateTableLayout();
                    }
                }.start();
            }
        }, 100);

    }

    @Override
    public void onBackPressed() {
        super.onBackPressed();
        if (playing) {
            stopLastSound();
        }
    }

    void populateTableLayout() {

        try {

            runOnUiThread(new Runnable() {

                @Override
                public void run() {

                    if (messages.size() == 0) {
                        populateMessageVector();
                    }
                    if (messages.size() > 0) {

                        TableLayout msgList = (TableLayout) findViewById(R.id.messagesLayout);

                        TableRow tr1 = new TableRow(messagesActivity.this);
                        TableRow.LayoutParams lp1 = new TableRow.LayoutParams(TableRow.LayoutParams.WRAP_CONTENT, TableRow.LayoutParams.MATCH_PARENT);
                        lp1.span = 3;
                        tr1.setBackgroundColor(Color.parseColor("#222222"));
                        CheckBox cb1 = new CheckBox(messagesActivity.this);
                        cb1.setChecked(messagesSelectedInPage());
                        cb1.setPadding(30, 10, 10, 10);
                        cb1.setOnClickListener(new View.OnClickListener() {

                            @Override
                            public void onClick(View v) {
                                CheckBox cb = (CheckBox) v;
                                toggleAllCheckboxes(cb.isChecked());
                            }

                        });
                        tr1.addView(cb1, lp1);
                        msgList.addView(tr1, lp1);

                        int n = 0;
                        for (int i = from; i < messages.size(); i++) {

                            Vector row = (Vector) messages.get(i);
                            String img = (String) row.get(0);

                            final TableRow trow = new TableRow(messagesActivity.this);
                            TableRow.LayoutParams lp = new TableRow.LayoutParams(TableRow.LayoutParams.WRAP_CONTENT, TableRow.LayoutParams.MATCH_PARENT);

                            if ((n % 2) == 0) {
                                trow.setBackgroundColor(Color.parseColor("#000000"));
                            } else {
                                trow.setBackgroundColor(Color.parseColor("#222222"));
                            }

                            if (n < maxMessages) {
                                CheckBox checkBox = new CheckBox(messagesActivity.this);
                                checkBox.setId(n);
                                checkBox.setChecked(messageSelected(i));
                                checkBox.setPadding(30, 10, 10, 10);
                                checkBox.setOnClickListener(new View.OnClickListener() {

                                    @Override
                                    public void onClick(View v) {
                                        updateSelectedMessageList(v.getId(), v);
                                    }

                                });
                                trow.addView(checkBox, lp);
                                checkBoxes.add(checkBox);

                                ImageView imgThumb = new ImageView(messagesActivity.this);
                                imgThumb.setId(n);
                                File imgFile = new File(img);
                                imgThumb.setImageBitmap(decodeSampledBitmapFromFile(imgFile.getAbsolutePath(), 150, 150));
                                imgThumb.setPadding(10, 10, 10, 10);
                                imgThumb.setOnClickListener(new View.OnClickListener() {

                                    @Override
                                    public void onClick(View v) {
                                        toggleOneCheckbox(v.getId());
                                    }

                                });
                                trow.addView(imgThumb, 150, 150);

                                ImageView imgSound = new ImageView(messagesActivity.this);
                                imgSound.setId(n);
                                imgSound.setImageResource(R.drawable.ic_play);
                                imgSound.setPadding(10, 10, 10, 10);
                                imgSound.setOnClickListener(new View.OnClickListener() {

                                    @Override
                                    public void onClick(View v) {
                                        toggleState(v.getId(), v);
                                    }

                                });
                                trow.addView(imgSound, 70, 70);

                            } else {
                                break;
                            }

                            trow.setGravity(Gravity.CENTER_VERTICAL);
                            msgList.addView(trow, lp);

                            n++;
                        }

                        if ((messages.size() > maxMessages) || (from > 0)) {

                            final TableRow trow = new TableRow(messagesActivity.this);
                            TableRow.LayoutParams lp = new TableRow.LayoutParams(TableRow.LayoutParams.WRAP_CONTENT, TableRow.LayoutParams.MATCH_PARENT);

                            if (from > 0) {
                                Button backButton = new Button(messagesActivity.this);
                                backButton.setBackgroundResource(R.drawable.button_background);
                                backButton.setTextColor(Color.BLACK);
                                backButton.setTextSize(TypedValue.COMPLEX_UNIT_DIP, 14);
                                backButton.setText(R.string.omPreviousPage);
                                backButton.setOnClickListener(new View.OnClickListener() {

                                    @Override
                                    public void onClick(View v) {
                                        previousPage();
                                    }

                                });
                                trow.addView(backButton, lp);
                            } else {
                                TextView textView1 = new TextView(messagesActivity.this);
                                textView1.setText(getBaseContext().getString(R.string.omEmpty));
                                textView1.setGravity(Gravity.CENTER_VERTICAL);
                                trow.addView(textView1, lp);
                            }

                            String fromText = String.valueOf(from + 1);
                            String toText = "";
                            if (messages.size() > (from + maxMessages)) {
                                toText = String.valueOf(from + maxMessages);
                            } else {
                                toText = String.valueOf(messages.size());
                            }
                            TextView textView = new TextView(messagesActivity.this);
                            textView.setTextColor(Color.WHITE);
                            textView.setTextSize(TypedValue.COMPLEX_UNIT_DIP, 14);
                            textView.setText(fromText + " - " + toText);
                            textView.setGravity(Gravity.CENTER);
                            trow.addView(textView, lp);

                            if (messages.size() > (from + maxMessages)) {
                                Button forwardButton = new Button(messagesActivity.this);
                                forwardButton.setBackgroundResource(R.drawable.button_background);
                                forwardButton.setTextColor(Color.BLACK);
                                forwardButton.setTextSize(TypedValue.COMPLEX_UNIT_DIP, 14);
                                forwardButton.setText(R.string.omNextPage);
                                forwardButton.setOnClickListener(new View.OnClickListener() {

                                    @Override
                                    public void onClick(View v) {
                                        nextPage();
                                    }

                                });
                                trow.addView(forwardButton, lp);
                            } else {
                                TextView textView2 = new TextView(messagesActivity.this);
                                textView2.setText(getBaseContext().getString(R.string.omEmpty));
                                textView2.setGravity(Gravity.CENTER_VERTICAL);
                                trow.addView(textView2, lp);
                            }

                            trow.setGravity(Gravity.CENTER_VERTICAL);
                            msgList.addView(trow, lp);

                        }
                    } else {
                        finishActivity();
                    }
                }
            });
            Thread.sleep(300);

        } catch (InterruptedException e) {

        }

    }

    public static int calculateInSampleSize(BitmapFactory.Options options, int reqWidth, int reqHeight) {
        final int height = options.outHeight;
        final int width = options.outWidth;
        int inSampleSize = 1;

        if (height > reqHeight || width > reqWidth) {

            final int halfHeight = height / 2;
            final int halfWidth = width / 2;

            while ((halfHeight / inSampleSize) >= reqHeight
                    && (halfWidth / inSampleSize) >= reqWidth) {
                inSampleSize *= 2;
            }
        }

        return inSampleSize;
    }

    public static Bitmap decodeSampledBitmapFromFile(String f, int reqWidth, int reqHeight) {

        final BitmapFactory.Options options = new BitmapFactory.Options();
        options.inJustDecodeBounds = true;
        File imgFile = new File(f);
        BitmapFactory.decodeFile(imgFile.getAbsolutePath(), options);

        options.inSampleSize = calculateInSampleSize(options, reqWidth, reqHeight);

        options.inJustDecodeBounds = false;
        return BitmapFactory.decodeFile(imgFile.getAbsolutePath(), options);
    }

    public String getPreference(String keyName) {
        String value = "";
        SharedPreferences ojoVozPrefs = getSharedPreferences("ojoVozPrefs", MODE_PRIVATE);
        value = ojoVozPrefs.getString(keyName, "");
        return value;
    }

    public void savePreference(String keyName, String keyValue, boolean append) {
        SharedPreferences ojoVozPrefs = getSharedPreferences("ojoVozPrefs", MODE_PRIVATE);
        SharedPreferences.Editor prefEditor = ojoVozPrefs.edit();
        if (append) {
            keyValue = ojoVozPrefs.getString(keyName, "") + keyValue;
        }
        prefEditor.putString(keyName, keyValue);
        prefEditor.commit();
    }

    private void populateMessageVector() {
        final String allMessages[];
        final String bundledMessages = getPreference("log");

        if (bundledMessages != "" && bundledMessages != null) {
            allMessages = bundledMessages.split("\\*");

            for (int i = 0; i < allMessages.length; i++) {
                Vector row = new Vector();
                String thisMessage = allMessages[i];
                if (!thisMessage.equals("") && thisMessage != null) {
                    String messageElements[] = thisMessage.split(";");

                    row.add(messageElements[4]);
                    soundPlayer s = new soundPlayer();
                    s.initialize(i, messageElements[5]);
                    row.add(s);

                    messages.add(row);
                    selectedMessages.add(i, 1);

                }
            }
        }
    }

    @Override
    public boolean onCreateOptionsMenu(android.view.Menu menu) {
        super.onCreateOptionsMenu(menu);
        menu.add(0, 0, 0, R.string.omSend);
        menu.add(1, 1, 1, R.string.omDelete);
        menu.add(2, 2, 2, R.string.omGoBack);
        menu.add(3, 3, 3, R.string.omGoToWebPage);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()) {
            case 0:
                if (!sending) {
                    if (messagesSelected()) {
                        if (isOnline()) {
                            if (playing) {
                                stopLastSound();
                            }
                            sendSelectedMessages();
                        } else {
                            Toast.makeText(this, R.string.omPleaseConnectText, Toast.LENGTH_SHORT).show();
                        }
                    } else {
                        Toast.makeText(this, R.string.omNoMessagesSelected, Toast.LENGTH_SHORT).show();
                    }
                }
                break;
            case 1:
                if (!sending) {
                    if (messagesSelected()) {
                        if (playing) {
                            stopLastSound();
                        }
                        showDialogDelete(this, getBaseContext().getString(R.string.omDelete), getBaseContext().getString(R.string.omDeleteSelectedMessages));
                    } else {
                        Toast.makeText(this, R.string.omNoMessagesSelected, Toast.LENGTH_SHORT).show();
                    }
                }
                break;
            case 2:
                if (!sending) {
                    if (playing) {
                        stopLastSound();
                    }
                    this.finish();
                }
                break;
            case 3:
                if (!sending){
                    openWebPage();
                }
        }
        return super.onOptionsItemSelected(item);
    }

    public void openWebPage(){
        if(isOnline()){
            server=getPreference("server");
            if(!server.isEmpty()){
                Intent i = new Intent(Intent.ACTION_VIEW);
                if(i.resolveActivity(getPackageManager())!=null) {
                    i.setData(Uri.parse(server));
                    startActivity(i);
                }
            } else {
                Toast.makeText(this, R.string.omPleaseConnectText, Toast.LENGTH_SHORT).show();
            }
        } else {
            Toast.makeText(this, R.string.omPleaseConnectText, Toast.LENGTH_SHORT).show();
        }
    }

    public void stopSoundPlayer() {
        if (lastPlayedId >= 0) {
            lastPlayedId = -1;
            lastPlayed = null;
        }
        if (sndPlayer != null) {
            sndPlayer.stop();
            sndPlayer.release();
            sndPlayer = null;
        }
    }

    public boolean isOnline() {
        ConnectivityManager cm = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
        NetworkInfo netInfo = cm.getActiveNetworkInfo();
        if (netInfo != null && netInfo.isConnected()) {
            return true;
        }
        return false;
    }

    public boolean messagesSelected() {
        for (int i = 0; i < selectedMessages.size(); i++) {
            if ((int) selectedMessages.get(i) == 1) {
                return true;
            }
        }
        return false;
    }

    public boolean messageSelected(int i) {
        if ((int) selectedMessages.get(i) == 1) {
            return true;
        } else {
            return false;
        }
    }

    public void stopLastSound() {
        Vector row = new Vector();

        if (lastPlayed != null && lastPlayedId != -1) {
            lastPlayed.setImageResource(R.drawable.ic_play);
            lastPlayed.invalidate();
            row = (Vector) messages.get(lastPlayedId);
            soundPlayer sndStop = (soundPlayer) row.get(1);
            sndStop.playing = false;
            stopSoundPlayer();
        }
        playing = false;
    }

    public void toggleState(int i, View v) {
        Vector row = (Vector) messages.get(i + from);
        soundPlayer snd = (soundPlayer) row.get(1);
        ImageView img = (ImageView) v;

        snd.toggle();

        if (snd.playing) {
            if (lastPlayed != null && lastPlayedId != (i + from)) {
                lastPlayed.setImageResource(R.drawable.ic_play);
                lastPlayed.invalidate();
                row = (Vector) messages.get(lastPlayedId);
                soundPlayer sndStop = (soundPlayer) row.get(1);
                sndStop.playing = false;
                stopSoundPlayer();
            }
            lastPlayed = img;
            lastPlayedId = img.getId() + from;

            if (sndPlayer != null) {
                sndPlayer = null;
            }

            try {
                sndPlayer = new MediaPlayer();
                sndPlayer.setDataSource(snd.filename);
                sndPlayer.prepare();
                sndPlayer.start();
                sndPlayer.setOnCompletionListener(new MediaPlayer.OnCompletionListener() {
                    @Override
                    public void onCompletion(MediaPlayer mp) {
                        if (sndPlayer != null) {
                            sndPlayer.stop();
                            sndPlayer.release();
                        }
                        if (lastPlayed != null) {
                            lastPlayed.setImageResource(R.drawable.ic_play);
                            lastPlayed.invalidate();
                        }
                        lastPlayed = null;
                        if (lastPlayedId >= 0) {
                            Vector row = (Vector) messages.get(lastPlayedId);
                            soundPlayer sndStop = (soundPlayer) row.get(1);
                            sndStop.playing = false;
                        }
                    }
                });
                playing = true;
            } catch (IOException e) {
            }

            img.setImageResource(R.drawable.ic_stop);
        } else {
            img.setImageResource(R.drawable.ic_play);
            stopSoundPlayer();
            playing = false;
        }
        img.invalidate();
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

    public void deleteSelectedMessages() {
        int n = 0;
        int upper;
        final String allMessages[];
        String newMessageList = "";
        final String bundledMessages = getPreference("log");

        if (bundledMessages != "" && bundledMessages != null) {
            allMessages = bundledMessages.split("\\*");

            if (allMessages.length > (from + maxMessages)) {
                upper = from+maxMessages;
            } else {
                upper = allMessages.length;
            }

            for (int i = 0; i < allMessages.length; i++) {
                if ((int) selectedMessages.get(i) == 1 && (i >= from && i < upper)) {
                    String messageElements[] = allMessages[i].split(";");
                    deleteFile(messageElements[4], true);
                    deleteFile(messageElements[5], false);
                } else {
                    newMessageList = newMessageList + allMessages[i] + "*";
                    n++;
                }
            }
            savePreference("log", newMessageList, false);
        }
        if (n > 0) {
            this.recreate();
        } else {
            clearDirectory(Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_PICTURES).getAbsolutePath() + File.separator + "ojovoz" + File.separator);
            this.finish();
        }

    }

    public void updateSelectedMessageList(int i, View v) {
        CheckBox c = (CheckBox) v;
        boolean isChecked = c.isChecked();

        if (isChecked) {
            selectedMessages.set(i + from, 1);
        } else {
            selectedMessages.set(i + from, 0);
        }
    }

    public void showDialogDelete(Activity activity, String title, CharSequence message) {
        AlertDialog.Builder builder = new AlertDialog.Builder(activity);

        if (title != null) builder.setTitle(title);
        builder.setMessage(message);
        builder.setNegativeButton(R.string.omCancelButtonText, null);
        builder.setPositiveButton(R.string.omOKButtonText, new DialogInterface.OnClickListener() {
            public void onClick(DialogInterface dialog, int id) {
                deleteSelectedMessages();
            }
        });
        builder.show();
    }

    public void toggleAllCheckboxes(boolean v) {
        for (int i = 0; i < checkBoxes.size(); i++) {
            CheckBox cb = (CheckBox) checkBoxes.get(i);
            cb.setChecked(v);
            int n = v ? 1 : 0;
            selectedMessages.set(i + from, n);
        }
    }

    public void toggleOneCheckbox(int n) {
        for (int i = 0; i < checkBoxes.size(); i++) {
            CheckBox cb = (CheckBox) checkBoxes.get(i);
            if (cb.getId() == n) {
                cb.setChecked(!cb.isChecked());
                int v = cb.isChecked() ? 1 : 0;
                selectedMessages.set(i + from, v);
                break;
            }
        }
    }

    public void previousPage() {
        if (playing) {
            stopLastSound();
        }
        from = from - maxMessages;
        newPage();
    }

    public void nextPage() {
        if (playing) {
            stopLastSound();
        }
        from = from + maxMessages;
        newPage();
    }

    public void newPage() {
        checkBoxes = new Vector();

        TableLayout msgList = (TableLayout) findViewById(R.id.messagesLayout);
        msgList.removeAllViewsInLayout();

        //Toast.makeText(this, R.string.omPleaseWait, Toast.LENGTH_SHORT).show();

        populateTableLayout();

        /*
        new Handler().postDelayed(new Runnable() {

            @Override
            public void run() {
                new Thread() {
                    @Override
                    public void run() {
                        populateTableLayout();
                    }
                }.start();
            }
        }, 50);
        */
    }

    public void sendSelectedMessages() {

        int dialogMax;
        final String allMessages[];
        String dialogTitle = getBaseContext().getString(R.string.omSendingMessagesDialogText);

        cancelUpload = false;
        sending = true;

        final String bundledMessages = getPreference("log");
        if (!server.equals("") && !phone_id.equals("")) {

            if(mail.equals("") || pass.equals("") || smtpServer.equals("") || smtpPort.equals("")){
                try {
                    new makeHTTPRequest().execute(server + "/mobile/get_email_settings.php?id=" + phone_id).get();
                } catch (InterruptedException e) {
                    mail = pass = smtpServer = smtpPort = "";
                } catch (ExecutionException e) {
                    mail = pass = smtpServer = smtpPort = "";
                }
            }

            if (!mail.equals("") && !pass.equals("") && !smtpServer.equals("") && !smtpPort.equals("")) {

                allMessages = bundledMessages.split("\\*");
                dialogMax = getNSelectedMessages();

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
                        cancelUpload = true;
                        upload.interrupt();
                        finishActivity();
                    }
                });

                upload = new Thread(new Runnable() {
                    public void run() {

                        for (int i = 0; i < allMessages.length; i++) {
                            if (!cancelUpload && isOnline()) {
                                String thisMessage = allMessages[i];
                                if (!thisMessage.equals("") && thisMessage != null && ((int) selectedMessages.get(i) == 1)) {
                                    String messageElements[] = thisMessage.split(";");
                                    Mail m = new Mail(mail, pass, smtpServer, smtpPort);
                                    String[] toArr = {mail};
                                    m.setTo(toArr);
                                    m.setFrom(mail);
                                    m.setSubject("ojovoz");
                                    m.setBody(messageElements[0] + ";" + messageElements[1] + ";" + messageElements[2] + ";" + messageElements[3] + ";" + messageElements[6]);
                                    boolean proceed = true;
                                    try {
                                        if (!messageElements[4].equals("null") && !messageElements[4].equals("")) {
                                            File f1 = new File(messageElements[4]);
                                            if (f1.exists()) {
                                                m.addAttachment(messageElements[4]);
                                            } else {
                                                proceed = false;
                                            }
                                        } else {
                                            proceed = false;
                                        }
                                        if (!messageElements[5].equals("null") && !messageElements[5].equals("")) {
                                            File f2 = new File(messageElements[5]);
                                            if (f2.exists()) {
                                                m.addAttachment(messageElements[5]);
                                            } else {
                                                proceed = false;
                                            }
                                        } else {
                                            proceed = false;
                                        }
                                    } catch (Exception e) {
                                        proceed = false;
                                    }
                                    if (proceed) {
                                        try {
                                            if (m.send()) {
                                                updateMessages(messageElements[1]);
                                                deleteFile(messageElements[4], true);
                                                deleteFile(messageElements[5], false);

                                            } else {
                                                cancelUpload = true;
                                                sending = false;
                                            }
                                        } catch (Exception e) {
                                            cancelUpload = true;
                                            sending = false;
                                        }
                                    } else {
                                        updateMessages(messageElements[1]);
                                        deleteFile(messageElements[4], true);
                                        deleteFile(messageElements[5], false);
                                    }
                                    progressHandler.sendMessage(progressHandler.obtainMessage());
                                }

                            } else {
                                sending = false;
                                upload.interrupt();
                                finishActivity();
                                break;
                            }
                        }
                        if (cancelUpload) {
                            finishActivity();
                        }
                    }
                });
                upload.start();
            } else {
                Toast.makeText(this, R.string.omPleaseConnectText, Toast.LENGTH_SHORT).show();
                sending = false;
            }
        } else {
            Toast.makeText(this, R.string.omPleaseConnectText, Toast.LENGTH_SHORT).show();
            sending = false;
        }

    }

    Handler progressHandler = new Handler() {
        @Override
        public void handleMessage(Message msg) {
            dialog.incrementProgressBy(uploadIncrement);
            if (dialog.getProgress() == dialog.getMax()) {
                sending = false;
                dialog.dismiss();
                upload.interrupt();
                messageCheck();
            }
        }
    };

    private void finishActivity() {
        this.finish();
    }

    private int getNSelectedMessages() {
        int n = 0;
        for (int i = 0; i < selectedMessages.size(); i++) {
            if ((int) selectedMessages.get(i) == 1) {
                n++;
            }
        }
        return n;
    }

    private boolean messagesSelectedInPage() {
        int n = 0;
        for (int i = from; i < selectedMessages.size(); i++) {
            if (i == from + maxMessages) {
                break;
            }
            if ((int) selectedMessages.get(i) == 1) {
                n++;
            }

        }
        if (n > 0) {
            return true;
        } else {
            return false;
        }
    }

    private void messageCheck() {
        String messageCheck = getPreference("log");
        if (messageCheck == "" || messageCheck == null) {
            clearDirectory(Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_PICTURES).getAbsolutePath() + File.separator + "ojovoz" + File.separator);
            this.finish();
        } else {
            this.recreate();
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

    private void updateMessages(String currentId) {
        String newMsg = "";
        String msg = getPreference("log");
        String allMessages[] = msg.split("\\*");
        for (int i = 0; i < allMessages.length; i++) {
            String messageElements[] = allMessages[i].split(";");
            if (messageElements.length == 7) {
                if (!messageElements[1].equals(currentId)) {
                    newMsg = newMsg + allMessages[i] + "*";
                }
            }
        }
        msg = newMsg;
        savePreference("log", msg, false);
    }

    private class makeHTTPRequest extends AsyncTask<String, Void, String> {


        @Override
        protected String doInBackground(String... params) {

            return getData(params[0]);

        }

        // onPostExecute displays the results of the AsyncTask.
        @Override
        protected void onPostExecute(String ret) {

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
                ret = "";
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
