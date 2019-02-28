package ojovoz.ojovoz;

import android.media.MediaRecorder;

import java.io.File;
import java.io.IOException;

/**
 * Created by Eugenio on 23/04/2018.
 */
public class audioRecorder {

    private MediaRecorder recorder;
    private String path="null";

    public audioRecorder() {
        recorder = new MediaRecorder();
    }

    public void modifyPath(String path) {
        this.path = path;
    }

    public void clear() {
        path="null";
    }

    public String getFilename() {
        return path;
    }

    public void start() throws IOException {
        String state = android.os.Environment.getExternalStorageState();
        if(!state.equals(android.os.Environment.MEDIA_MOUNTED))  {
            throw new IOException("SD Card is not mounted.  It is " + state + ".");
        }

        // make sure the directory we plan to store the recording in exists
        File directory = new File(path).getParentFile();
        if (!directory.exists() && !directory.mkdirs()) {
            throw new IOException("Path to file could not be created.");
        }

        recorder.setAudioSource(MediaRecorder.AudioSource.MIC);
        recorder.setOutputFormat(MediaRecorder.OutputFormat.AMR_NB);
        recorder.setAudioEncoder(MediaRecorder.AudioEncoder.AMR_NB);
        recorder.setOutputFile(path);
        try {
            recorder.prepare();
        } catch (IllegalStateException e) {
            //
        }
        recorder.start();
    }

    public void stop() throws IOException {
        recorder.stop();
        recorder.reset();
        recorder.release();
    }

}
