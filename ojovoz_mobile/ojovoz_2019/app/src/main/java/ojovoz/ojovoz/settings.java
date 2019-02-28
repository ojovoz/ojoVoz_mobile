package ojovoz.ojovoz;

import android.app.ProgressDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.os.Bundle;
import android.os.Environment;
import android.support.v7.app.AlertDialog;
import android.support.v7.app.AppCompatActivity;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.io.StringReader;
import java.util.ArrayList;
import java.util.Iterator;

import au.com.bytecode.opencsv.CSVReader;
import au.com.bytecode.opencsv.CSVWriter;

/**
 * Created by Eugenio on 25/02/2019.
 */
public class settings extends AppCompatActivity implements httpConnection.AsyncResponse {

    public preferenceManager prefs;
    public String server;
    public String user;
    public String phoneID;

    boolean bConnecting = false;
    boolean bExit=false;

    EditText st;
    EditText pt;
    EditText ut;

    ProgressDialog downloadingParamsDialog;
    ProgressDialog downloadingTagsDialog;
    int connectionAction;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_settings);

        bConnecting = false;
        prefs = new preferenceManager(this);
        server = prefs.getPreference("server");
        user = prefs.getPreference("user");
        phoneID = prefs.getPreference("phoneID");

        st = (EditText) findViewById(R.id.serverURL);
        st.setText(server);

        pt = (EditText) findViewById(R.id.phoneID);
        pt.setText(phoneID);

        ut = (EditText) findViewById(R.id.userName);
        ut.setText(user);

        Button tb = (Button)findViewById(R.id.downloadTags);
        tb.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                downloadTags();
            }
        });
    }

    @Override
    public void onBackPressed() {
        tryExit();
    }

    @Override
    public boolean onCreateOptionsMenu(android.view.Menu menu) {
        super.onCreateOptionsMenu(menu);
        menu.add(0, 0, 0, R.string.opGoBack);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch(item.getItemId()){
            case 0:
                tryExit();
                break;
        }
        return super.onOptionsItemSelected(item);
    }

    public void tryExit(){
        if (!st.getText().toString().equals(server) || !pt.getText().toString().equals(phoneID) || !ut.getText().toString().equals(user)) {
            confirmExit();
        } else {
            goToPictureSound();
        }
    }

    public void confirmExit() {
        AlertDialog.Builder exitDialog = new AlertDialog.Builder(this);
        exitDialog.setMessage(R.string.settingsNotSavedText);
        exitDialog.setNegativeButton(R.string.noButtonText, null);
        exitDialog.setPositiveButton(R.string.yesButtonText, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialogInterface, int i) {
                goToPictureSound();
            }
        });
        exitDialog.create();
        exitDialog.show();
    }

    public void goToPictureSound() {
        final Context context = this;
        Intent i = new Intent(context, pictureSound.class);
        startActivity(i);
        finish();
    }

    public void clearMessages(View v){
        AlertDialog.Builder exitDialog = new AlertDialog.Builder(this);
        exitDialog.setMessage(R.string.confirmClearMessages);
        exitDialog.setNegativeButton(R.string.noButtonText, null);
        exitDialog.setPositiveButton(R.string.yesButtonText, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialogInterface, int i) {
                doClearMessages();
            }
        });
        exitDialog.create();
        exitDialog.show();
    }

    public void doClearMessages(){
        ArrayList<oLog> logList;
        ArrayList<String> deleteFiles = new ArrayList<>();

        oLog log = new oLog(this);
        logList = log.createLog();
        int[] logDeleteList = new int[logList.size()];
        Iterator<oLog> iterator = logList.iterator();
        int n=0;
        while(iterator.hasNext()){
            log=iterator.next();
            logDeleteList[n]=log.line;
            deleteFiles.add(log.pictureFile);
            deleteFiles.add(log.soundFile);
            n++;
        }
        if(deleteFiles.size()>0){
            deleteImgSndFiles(deleteFiles);
            log.deleteLogItems(logDeleteList);
            Toast.makeText(this, R.string.messagesDeleted, Toast.LENGTH_SHORT).show();
        } else {
            Toast.makeText(this, R.string.noMessages, Toast.LENGTH_SHORT).show();
        }
    }

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

    public String checkServer(String s){
        String ret=s;
        if (!s.startsWith("http://")) {
            ret = "http://" + s;
        }
        return ret;
    }

    public void saveSettings(View v){
        server=checkServer(st.getText().toString());
        prefs.savePreference("server",server);
        phoneID=pt.getText().toString();
        prefs.savePreference("phoneID",phoneID);
        user=ut.getText().toString();
        prefs.savePreference("user",user);
        csvFileManager paramList;
        paramList = new csvFileManager("parameters");
        paramList.deleteCSVFile(this);

        httpConnection http = new httpConnection(this, this);
        if (http.isOnline()) {
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
            connectionAction=0;
            bConnecting=true;
            bExit=true;
            http.execute(server + "/mobile/get_parameters.php?id=" + phoneID, "");
        }
    }

    public void downloadTags(){
        server=checkServer(st.getText().toString());
        phoneID=pt.getText().toString();

        httpConnection http = new httpConnection(this, this);
        if (http.isOnline()) {
            CharSequence dialogTitle = getString(R.string.downloadingTagsMessage);
            downloadingTagsDialog = new ProgressDialog(this);
            downloadingTagsDialog.setCancelable(true);
            downloadingTagsDialog.setCanceledOnTouchOutside(false);
            downloadingTagsDialog.setMessage(dialogTitle);
            downloadingTagsDialog.setIndeterminate(true);
            downloadingTagsDialog.show();
            downloadingTagsDialog.setOnCancelListener(new DialogInterface.OnCancelListener() {
                @Override
                public void onCancel(DialogInterface d) {
                    bConnecting = false;
                    downloadingTagsDialog.dismiss();
                }
            });
            connectionAction=1;
            bConnecting=true;
            http.execute(server + "/mobile/get_tags.php?id=" + phoneID, "");
        } else {
            Toast.makeText(this, R.string.pleaseConnectMessage, Toast.LENGTH_SHORT).show();
        }
    }

    @Override
    public void processFinish(String output) {
        String[] nextLine;
        CSVReader reader;
        File file;

        switch(connectionAction){
            case 0:
                downloadingParamsDialog.dismiss();
                reader = new CSVReader(new StringReader(output), ',', '"');
                this.deleteFile("parameters");
                file = new File(this.getFilesDir(), "parameters");
                try {
                    FileWriter w = new FileWriter(file);
                    CSVWriter writer = new CSVWriter(w, ',', '"');
                    while ((nextLine = reader.readNext()) != null) {
                        writer.writeNext(nextLine);
                    }
                    writer.close();
                    reader.close();
                } catch (IOException e) {
                }
                bConnecting = false;
                downloadTags();
                break;
            case 1:
                downloadingTagsDialog.dismiss();
                String[] outputParts = output.split(";");
                this.deleteFile("tags");
                file = new File(this.getFilesDir(), "tags");
                try {
                    FileWriter w = new FileWriter(file);
                    CSVWriter writer = new CSVWriter(w, ',', '"');
                    for(int i=0;i<outputParts.length;i++){
                        nextLine= new String[] {outputParts[i]};
                        writer.writeNext(nextLine);
                    }
                    writer.close();
                    if(bExit){
                        goToPictureSound();
                    }
                } catch (IOException e) {
                }
                bConnecting = false;
                break;
        }
    }
}
