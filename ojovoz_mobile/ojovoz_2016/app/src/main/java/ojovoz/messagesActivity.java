package ojovoz;

import android.app.Activity;
import android.content.SharedPreferences;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.os.Bundle;
import android.widget.CheckBox;
import android.widget.ImageView;
import android.widget.TableLayout;
import android.widget.TableRow;

import java.io.File;
import java.util.Vector;

import ojovoz.skeleton.R;


/**
 * Created by Eugenio on 19/07/2016.
 */
public class messagesActivity extends Activity {

    Vector messages=null;

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.messages);

        messages = new Vector();
        if (populateMessageVector()) {
            TableLayout msgList = (TableLayout) findViewById(R.id.messagesLayout);
            for (int i = 0; i < messages.size(); i++) {

                Vector row = (Vector) messages.get(i);
                String img = (String) row.get(0);
                soundPlayer snd = (soundPlayer) row.get(1);

                TableRow trow = new TableRow(this);
                TableRow.LayoutParams lp = new TableRow.LayoutParams(TableRow.LayoutParams.WRAP_CONTENT, TableRow.LayoutParams.MATCH_PARENT);

                CheckBox checkBox = new CheckBox(this);
                checkBox.setId(i);
                checkBox.setChecked(true);
                trow.addView(checkBox,lp);

                /*
                TextView textView = new TextView(this);
                textView.setId(i+100);
                textView.setText(img);
                trow.addView(textView,lp);
                */

                ImageView imgThumb = new ImageView(this);
                imgThumb.setId(i+100);
                File imgFile = new File(img);
                imgThumb.setImageBitmap(decodeSampledBitmapFromFile(imgFile.getAbsolutePath(),150,150));
                imgThumb.setPadding(10,10,10,10);
                trow.addView(imgThumb,150,150);

                msgList.addView(trow, lp);

            }
        }
    }

    public int calculateInSampleSize(BitmapFactory.Options options, int reqWidth, int reqHeight) {
        int height = options.outHeight;
        int width = options.outWidth;
        int inSampleSize = 1;

        if (height > reqHeight || width > reqWidth) {

            int halfHeight = height / 2;
            int halfWidth = width / 2;

            while ((halfHeight / inSampleSize) >= reqHeight
                    && (halfWidth / inSampleSize) >= reqWidth) {
                inSampleSize *= 2;
            }
        }

        return inSampleSize;
    }

    public Bitmap decodeSampledBitmapFromFile(String f, int reqWidth, int reqHeight) {

        BitmapFactory.Options options = new BitmapFactory.Options();
        options.inJustDecodeBounds = true;
        File imgFile = new File(f);
        Bitmap thumb = BitmapFactory.decodeFile(imgFile.getAbsolutePath());

        options.inSampleSize = calculateInSampleSize(options, reqWidth, reqHeight);

        options.inJustDecodeBounds = false;
        thumb = BitmapFactory.decodeFile(imgFile.getAbsolutePath());
        return thumb;
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
}
