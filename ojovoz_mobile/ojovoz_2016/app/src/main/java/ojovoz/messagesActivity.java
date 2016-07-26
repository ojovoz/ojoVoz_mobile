package ojovoz;

import android.app.Activity;
import android.app.AlertDialog;
import android.content.DialogInterface;
import android.content.SharedPreferences;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.Color;
import android.media.MediaPlayer;
import android.os.Bundle;
import android.os.Environment;
import android.os.Handler;
import android.view.Gravity;
import android.view.MenuItem;
import android.view.View;
import android.widget.CheckBox;
import android.widget.ImageView;
import android.widget.TableLayout;
import android.widget.TableRow;
import android.widget.TextView;
import android.widget.Toast;

import java.io.File;
import java.io.IOException;
import java.util.Vector;

import ojovoz.skeleton.R;


/**
 * Created by Eugenio on 19/07/2016.
 */
public class messagesActivity extends Activity {

    Vector messages = null;
    Vector selectedMessages = new Vector();
    int maxMessages = 20;

    ImageView lastPlayed = null;
    int lastPlayedId;
    MediaPlayer sndPlayer = new MediaPlayer();

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.messages);

        Toast.makeText(this, R.string.omPleaseWait, Toast.LENGTH_SHORT).show();

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


    }

    void populateTableLayout() {

        try {

            runOnUiThread(new Runnable() {

                @Override
                public void run() {

                    messages = new Vector();
                    if (populateMessageVector()) {
                        TableLayout msgList = (TableLayout) findViewById(R.id.messagesLayout);
                        for (int i = 0; i < messages.size(); i++) {

                            Vector row = (Vector) messages.get(i);
                            String img = (String) row.get(0);

                            final TableRow trow = new TableRow(messagesActivity.this);
                            TableRow.LayoutParams lp = new TableRow.LayoutParams(TableRow.LayoutParams.WRAP_CONTENT, TableRow.LayoutParams.MATCH_PARENT);

                            if ((i % 2) == 0) {
                                trow.setBackgroundColor(Color.parseColor("#000000"));
                            } else {
                                trow.setBackgroundColor(Color.parseColor("#111111"));
                            }

                            CheckBox checkBox = new CheckBox(messagesActivity.this);
                            checkBox.setId(i);
                            checkBox.setChecked(true);
                            checkBox.setPadding(30, 10, 10, 10);
                            checkBox.setOnClickListener(new View.OnClickListener() {

                                @Override
                                public void onClick(View v) {
                                    updateSelectedMessageList(v.getId(), v);
                                }

                            });
                            trow.addView(checkBox, lp);

                            if (i == maxMessages) {

                                TextView textView = new TextView(messagesActivity.this);
                                textView.setId(i);
                                int remaining = messages.size() - maxMessages;
                                textView.setText("+ " + String.valueOf(remaining) + " " + getBaseContext().getString(R.string.omRemainingMessages));
                                textView.setGravity(Gravity.CENTER_VERTICAL);
                                trow.addView(textView, lp);

                            } else {

                                ImageView imgThumb = new ImageView(messagesActivity.this);
                                imgThumb.setId(i);
                                File imgFile = new File(img);
                                imgThumb.setImageBitmap(decodeSampledBitmapFromFile(imgFile.getAbsolutePath(), 150, 150));
                                imgThumb.setPadding(10, 10, 10, 10);
                                trow.addView(imgThumb, 150, 150);

                                ImageView imgSound = new ImageView(messagesActivity.this);
                                imgSound.setId(i);
                                imgSound.setImageResource(R.drawable.ic_play);
                                imgSound.setPadding(10, 10, 10, 10);
                                imgSound.setOnClickListener(new View.OnClickListener() {

                                    @Override
                                    public void onClick(View v) {
                                        toggleState(v.getId(), v);
                                    }

                                });
                                trow.addView(imgSound, 70, 70);
                            }

                            trow.setGravity(Gravity.CENTER_VERTICAL);

                            msgList.addView(trow, lp);

                            if (i == maxMessages) {
                                break;
                            }

                        }
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

    /*
    playing sounds: https://www.badprog.com/android-mediaplayer-example-of-playing-sounds
     */

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

    private boolean populateMessageVector() {
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
                    selectedMessages.add(i,1);
                }
            }
            return true;
        } else {
            return false;
        }
    }

    @Override
    public boolean onCreateOptionsMenu(android.view.Menu menu) {
        super.onCreateOptionsMenu(menu);
        menu.add(0, 0, 0, R.string.omSend);
        menu.add(1, 1, 1, R.string.omDelete);
        menu.add(2, 2, 2, R.string.omCancelButtonText);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()) {
            case 0:
                break;
            case 1:
                showDialogDelete(this,getBaseContext().getString(R.string.omDelete),getBaseContext().getString(R.string.omDeleteSelectedMessages));
                break;
            case 2:
                this.finish();
        }
        return super.onOptionsItemSelected(item);
    }

    public void toggleState(int i, View v) {
        Vector row = (Vector) messages.get(i);
        soundPlayer snd = (soundPlayer) row.get(1);
        ImageView img = (ImageView) v;

        snd.toggle();

        if (snd.playing) {
            if (lastPlayed != null && lastPlayedId != i) {
                lastPlayed.setImageResource(R.drawable.ic_play);
                lastPlayed.invalidate();
                row = (Vector) messages.get(lastPlayedId);
                soundPlayer sndStop = (soundPlayer) row.get(1);
                sndStop.playing = false;
                sndPlayer.stop();
                sndPlayer.release();
                sndPlayer = null;
            }
            lastPlayed = img;
            lastPlayedId = img.getId();

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
                        sndPlayer.stop();
                        sndPlayer.release();
                        lastPlayed.setImageResource(R.drawable.ic_play);
                        lastPlayed.invalidate();
                        lastPlayed = null;
                        Vector row = (Vector) messages.get(lastPlayedId);
                        soundPlayer sndStop = (soundPlayer) row.get(1);
                        sndStop.playing = false;
                    }
                });
            } catch (IOException e) {
            }

            img.setImageResource(R.drawable.ic_stop);
        } else {
            img.setImageResource(R.drawable.ic_play);
            lastPlayed = null;
            sndPlayer.stop();
            sndPlayer.release();
            sndPlayer = null;
        }
        img.invalidate();
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

    public void deleteSelectedMessages() {
        final String allMessages[];
        String newMessageList = "";
        final String bundledMessages = getPreference("log");

        if (bundledMessages != "" && bundledMessages != null) {
            allMessages = bundledMessages.split("\\*");

            for (int i = 0; i < allMessages.length; i++) {
                if(i<maxMessages){
                    if((int)selectedMessages.get(i)==1){
                        String messageElements[] = allMessages[i].split(";");
                        deleteFileOM(messageElements[4],true);
                        deleteFileOM(messageElements[5],false);
                    } else {
                        newMessageList = newMessageList + allMessages[i] + "*";
                    }
                } else {
                    if((int)selectedMessages.get(maxMessages)==1){
                        String messageElements[] = allMessages[i].split(";");
                        deleteFileOM(messageElements[4],true);
                        deleteFileOM(messageElements[5],false);
                    } else {
                        newMessageList = newMessageList + allMessages[i] + "*";
                    }
                }
            }
            savePreference("log",newMessageList,false);
        }
        this.finish();
    }

    public void updateSelectedMessageList(int i, View v) {
        CheckBox c = (CheckBox) v;
        boolean isChecked = c.isChecked();

        if (isChecked) {
            selectedMessages.set(i, 1);
        } else {
            selectedMessages.set(i, 0);
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
}
