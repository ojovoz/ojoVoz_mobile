package ojovoz.ojovoz;

import android.app.Dialog;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.media.MediaPlayer;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.net.Uri;
import android.os.Bundle;
import android.os.Environment;
import android.os.Handler;
import android.os.Message;
import android.support.v4.content.ContextCompat;
import android.support.v7.app.AlertDialog;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.MenuItem;
import android.view.View;
import android.view.Window;
import android.widget.CheckBox;
import android.widget.ImageView;
import android.widget.Toast;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileWriter;
import java.io.IOException;
import java.io.InputStream;
import java.io.StringReader;
import java.util.ArrayList;
import java.util.Iterator;
import java.util.List;

import au.com.bytecode.opencsv.CSVReader;
import au.com.bytecode.opencsv.CSVWriter;

/**
 * Created by Eugenio on 21/02/2019.
 */
public class messages extends AppCompatActivity implements httpConnection.AsyncResponse {

    public int displayWidth;
    public int displayHeight;

    oRecyclerViewAdapter recyclerViewAdapter;
    public ArrayList<oLog> logList;

    boolean soundPlaying;
    MediaPlayer soundPlayer;

    public int nSelected;

    public preferenceManager prefs;
    public String server;
    public String phoneID;

    boolean bConnecting;
    private ProgressDialog sendingMultimediaDialog;
    ProgressDialog downloadingParamsDialog;
    private Thread uploadMultimedia;
    private int[] multimediaCleanUpList;
    private ArrayList<String> deleteFiles = new ArrayList<>();

    String ojoVozEmail = "";
    String ojoVozPass = "";
    String multimediaSubject = "";
    String smtpServer = "";
    String smtpPort = "";

    private Context recordsContext;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_messages);

        bConnecting = false;

        prefs = new preferenceManager(this);
        server = prefs.getPreference("server");
        phoneID = prefs.getPreference("phoneID");

        recordsContext = this;

        displayWidth = getIntent().getExtras().getInt("displayWidth");
        displayHeight = getIntent().getExtras().getInt("displayHeight");

        fillRecyclerView();
    }

    @Override
    public void onBackPressed() {
        goBack();
    }

    @Override
    public boolean onPrepareOptionsMenu(android.view.Menu menu) {
        menu.clear();
        if (nSelected > 0) {
            menu.add(0, 0, 0, R.string.opDeleteSelectedMessages);
            menu.add(1, 1, 1, R.string.opUploadSelectedMessages);
        }
        menu.add(2, 2, 2, R.string.opToggleSelection);
        menu.add(3, 3, 3, R.string.opGoToWeb);
        menu.add(4, 4, 4, R.string.opGoBack);
        return super.onPrepareOptionsMenu(menu);
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()) {
            case 0:
                tryDeleteSelectedItems();
                break;
            case 1:
                uploadRecords();
                break;
            case 2:
                toggleSelection();
                break;
            case 3:
                goToWebPage();
                break;
            case 4:
                goBack();
        }
        return super.onOptionsItemSelected(item);
    }

    public void uploadRecords() {
        if (!bConnecting) {
            httpConnection http = new httpConnection(this, this);
            if (http.isOnline()) {
                bConnecting = true;
                sendMessages();
            } else {
                Toast.makeText(this, R.string.pleaseConnectMessage, Toast.LENGTH_SHORT).show();
            }
        }
    }

    public void sendMessages() {
        httpConnection http = new httpConnection(this, this);
        if (http.isOnline()) {
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
        List<oCardData> list = recyclerViewAdapter.list;
        Iterator<oCardData> iterator = list.iterator();

        while (iterator.hasNext()) {
            oCardData cd = iterator.next();
            if (cd.isSelected) {
                oLog thisRecord = logList.get(cd.line);
                b.add(thisRecord.toString(";"));
                attachments.add(thisRecord);
            }
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
                        createLogList();
                        recyclerViewAdapter.list = cardDataFromLog();
                        recyclerViewAdapter.setList(recyclerViewAdapter.list);
                        recyclerViewAdapter.notifyDataSetChanged();
                        nSelected = 0;
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

                oLog l = new oLog(recordsContext);
                l.deleteLogItems(multimediaCleanUpList);

                deleteImgSndFiles(deleteFiles);

                createLogList();
                if(logList.size()>0) {
                    recyclerViewAdapter.list = cardDataFromLog();
                    recyclerViewAdapter.setList(recyclerViewAdapter.list);
                    recyclerViewAdapter.notifyDataSetChanged();
                    modifyActivityTitle();
                } else {
                    goBack();
                }
            }
        }
    };

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

    public void tryDeleteSelectedItems() {
        stopSoundPlayer();
        if (nSelected > 0) {
            AlertDialog.Builder confirmDialog = new AlertDialog.Builder(this);
            confirmDialog.setMessage(R.string.deleteItemsConfirmMessage);
            confirmDialog.setNegativeButton(R.string.noButtonText, null);
            confirmDialog.setPositiveButton(R.string.yesButtonText, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialogInterface, int i) {
                    deleteSelectedItems();

                }
            });
            confirmDialog.create();
            confirmDialog.show();
        } else {
            Toast.makeText(this, R.string.noItemsSelectedMessage, Toast.LENGTH_SHORT).show();
        }
    }

    public void deleteSelectedItems() {
        int[] delete = new int[recyclerViewAdapter.list.size()];
        ArrayList<String> deleteFiles = new ArrayList<>();
        List<oCardData> list = recyclerViewAdapter.list;
        Iterator<oCardData> iterator = list.iterator();
        int n = 0;
        while (iterator.hasNext()) {
            oCardData cd = iterator.next();
            if (cd.isSelected) {
                delete[n] = logList.get(cd.line).line;
                if (!cd.imgFile.isEmpty()) {
                    deleteFiles.add(cd.imgFile);
                }
                if (!cd.sndFile.isEmpty()) {
                    deleteFiles.add(cd.sndFile);
                }
            } else {
                delete[n] = -1;
            }
            n++;
        }
        oLog l = new oLog(this);
        l.deleteLogItems(delete);
        deleteImgSndFiles(deleteFiles);
        createLogList();
        if(logList.size()>0) {
            recyclerViewAdapter.list = cardDataFromLog();
            recyclerViewAdapter.setList(recyclerViewAdapter.list);
            recyclerViewAdapter.notifyDataSetChanged();
            modifyActivityTitle();
        } else {
            goBack();
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

    public void goBack() {
        stopSoundPlayer();
        final Context context = this;
        Intent i = new Intent(context, pictureSound.class);
        startActivity(i);
        finish();
    }

    public void fillRecyclerView() {
        createLogList();
        nSelected = logList.size();
        List<oCardData> data = cardDataFromLog();
        RecyclerView recyclerView = (RecyclerView) findViewById(R.id.recyclerView);
        recyclerViewAdapter = new oRecyclerViewAdapter(data, getApplication());
        recyclerView.setAdapter(recyclerViewAdapter);
        recyclerView.setLayoutManager(new LinearLayoutManager(this));
    }

    public void createLogList() {
        oLog log = new oLog(this);
        logList = log.sortLogByDate(log.createLog(), true, -1);
        nSelected = logList.size();
        modifyActivityTitle();
    }

    public List<oCardData> cardDataFromLog() {
        dateHelper dH = new dateHelper();
        List<oCardData> ret = new ArrayList<>();
        Iterator<oLog> logIterator = logList.iterator();
        int n = 0;
        while (logIterator.hasNext()) {
            oLog l = logIterator.next();
            String tags = l.tags.replaceAll(",",", ");
            oCardData c = new oCardData();
            c.line = n;
            if (n % 2 == 0) {
                c.backColor = ContextCompat.getColor(this, R.color.colorFillFaded);
            } else {
                c.backColor = ContextCompat.getColor(this, R.color.colorWhite);
            }
            c.info = dH.dateToString(l.date);
            c.info += (!tags.isEmpty()) ? "\n" + tags : "";
            c.imgFile = l.pictureFile;
            c.sndFile = l.soundFile;
            c.isSelected = true;

            ret.add(c);
            n++;
        }
        return ret;
    }

    public void viewImage(View v) {
        int n = (int) v.getTag();
        oLog l = logList.get(n);

        Bitmap picture = scaleBitmap(l.pictureFile);

        final Dialog dialog = new Dialog(this);
        dialog.requestWindowFeature(Window.FEATURE_NO_TITLE);
        dialog.setContentView(R.layout.dialog_view_picturesound);
        dialog.setCanceledOnTouchOutside(true);
        dialog.setCancelable(true);

        ImageView i = (ImageView) dialog.findViewById(R.id.imageView);
        i.setMaxWidth((int) (displayWidth * .8f));
        i.setMaxHeight((int) (displayHeight * .8f));
        i.setImageBitmap(picture);

        final String s = l.soundFile;

        ImageView player = (ImageView) dialog.findViewById(R.id.playStopButton);
        player.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                if (soundPlaying && soundPlayer != null) {
                    soundPlayer.stop();
                    soundPlayer.release();
                    ImageView player = (ImageView) view;
                    player.setImageResource(R.drawable.play);
                    player.invalidate();
                    soundPlaying = !soundPlaying;
                } else {
                    final ImageView player = (ImageView) view;
                    player.setImageResource(R.drawable.stop);
                    player.invalidate();
                    try {
                        soundPlayer = new MediaPlayer();
                        soundPlayer.setDataSource(s);
                        soundPlayer.prepare();
                        soundPlayer.start();
                        soundPlayer.setOnCompletionListener(new MediaPlayer.OnCompletionListener() {
                            @Override
                            public void onCompletion(MediaPlayer m) {
                                if (soundPlayer != null) {
                                    soundPlayer.stop();
                                    soundPlayer.release();
                                    player.setImageResource(R.drawable.play);
                                    player.invalidate();
                                    soundPlaying = false;
                                }
                            }
                        });
                        soundPlaying = !soundPlaying;
                    } catch (IOException e) {

                    }
                }

            }
        });

        dialog.setOnDismissListener(new DialogInterface.OnDismissListener() {
            @Override
            public void onDismiss(DialogInterface dialogInterface) {
                stopSoundPlayer();
            }
        });

        dialog.show();
    }

    public Bitmap scaleBitmap(String path) {
        Bitmap ret = null;
        final int IMAGE_MAX_SIZE = 400000;
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

    void stopSoundPlayer() {
        if (soundPlaying && soundPlayer != null) {
            soundPlayer.stop();
            soundPlayer.release();
            soundPlaying = false;
        }
    }

    public void selectItem(View v) {
        int n = (int) v.getTag();
        CheckBox cb = (CheckBox) v;
        recyclerViewAdapter.list.get(n).isSelected = cb.isChecked();

        nSelected = (cb.isChecked()) ? nSelected + 1 : nSelected - 1;
        modifyActivityTitle();
        invalidateOptionsMenu();
    }

    public void toggleSelection(){
        Iterator<oCardData> iterator = recyclerViewAdapter.list.iterator();
        nSelected=logList.size();
        while(iterator.hasNext()){
            oCardData cd = iterator.next();
            cd.isSelected = !cd.isSelected;
            nSelected = !(cd.isSelected) ? nSelected-1 : nSelected;
        }

        modifyActivityTitle();
        invalidateOptionsMenu();
        recyclerViewAdapter.notifyDataSetChanged();
    }

    public void modifyActivityTitle() {
        if (nSelected > 0) {
            setTitle(getString(R.string.messagesActivity) + ": " + String.valueOf(nSelected) + " " + getString(R.string.selected));
        } else {
            setTitle(getString(R.string.messagesActivity));
        }
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
