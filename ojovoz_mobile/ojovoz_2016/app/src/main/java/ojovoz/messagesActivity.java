package ojovoz;

import android.app.Activity;
import android.content.SharedPreferences;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.Color;
import android.os.Bundle;
import android.os.Handler;
import android.view.Gravity;
import android.view.MenuItem;
import android.view.View;
import android.widget.CheckBox;
import android.widget.ImageView;
import android.widget.TableLayout;
import android.widget.TableRow;
import android.widget.Toast;

import java.io.File;
import java.util.Vector;

import ojovoz.skeleton.R;


/**
 * Created by Eugenio on 19/07/2016.
 */
public class messagesActivity extends Activity {

    Vector messages = null;

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.messages);

        Toast.makeText(this, R.string.omPleaseWait, Toast.LENGTH_SHORT).show();

        new Handler().postDelayed(new Runnable(){

            @Override
            public void run() {
                new Thread() {
                    @Override
                    public void run() {
                        populateTableLayout();
                    }
                }.start();
            }
        },50);


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
                            trow.addView(checkBox, lp);

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

                            trow.setGravity(Gravity.CENTER_VERTICAL);

                            msgList.addView(trow, lp);

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
        BitmapFactory.decodeFile(imgFile.getAbsolutePath(),options);

        options.inSampleSize = calculateInSampleSize(options, reqWidth, reqHeight);

        options.inJustDecodeBounds = false;
        return BitmapFactory.decodeFile(imgFile.getAbsolutePath(),options);
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
            img.setImageResource(R.drawable.ic_stop);
        } else {
            img.setImageResource(R.drawable.ic_play);
        }
        img.invalidate();
    }
}
