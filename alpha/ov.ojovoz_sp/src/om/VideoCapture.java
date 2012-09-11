package om;

import java.io.IOException;

import om.skeleton.R;
import android.app.Activity;
import android.content.pm.ActivityInfo;
import android.media.CamcorderProfile;
import android.media.MediaRecorder;
import android.os.Bundle;
import android.os.Environment;
import android.util.Log;
import android.view.SurfaceHolder;
import android.view.SurfaceView;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.Window;
import android.view.WindowManager;

public class VideoCapture extends Activity implements OnClickListener,
		SurfaceHolder.Callback {

	MediaRecorder recorder;
	SurfaceHolder holder;
	boolean recording = false;
	
	private static final String TAG = "VideoCapture";

	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		Log.v(TAG, "after super.onCreate");
		requestWindowFeature(Window.FEATURE_NO_TITLE);
		Log.v(TAG, "after requestWindowFieature");
		getWindow().setFlags(WindowManager.LayoutParams.FLAG_FULLSCREEN,
				WindowManager.LayoutParams.FLAG_FULLSCREEN);
		setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_LANDSCAPE);
		
		recorder = new MediaRecorder();
		Log.v(TAG, "before initRecorder");
		initRecorder();
		Log.v(TAG, "after initRecorder");
		setContentView(R.layout.activity_video_capture);

		SurfaceView cameraView = (SurfaceView) findViewById(R.id.omVideoView);
		Log.v(TAG, "after get camera view");
		holder = cameraView.getHolder();
		holder.addCallback(this);
		holder.setType(SurfaceHolder.SURFACE_TYPE_PUSH_BUFFERS);

		cameraView.setClickable(true);
		cameraView.setOnClickListener(this);
		Log.v(TAG, "onCreate finished");
	}

	private void initRecorder() {
		Log.v(TAG, "in initRecorder");
		recorder.setAudioSource(MediaRecorder.AudioSource.DEFAULT);
		Log.v(TAG, "set audio src");
		recorder.setVideoSource(MediaRecorder.VideoSource.DEFAULT);
		Log.v(TAG, "set video src");

		CamcorderProfile cpHigh = CamcorderProfile
				.get(CamcorderProfile.QUALITY_HIGH);
		recorder.setProfile(cpHigh);
		recorder.setOutputFile(Environment.getExternalStorageDirectory().getPath() + "/videocapture_example.mp4");
		recorder.setMaxDuration(50000); // 50 seconds
		recorder.setMaxFileSize(5000000); // Approximately 5 megabytes
		Log.v(TAG, "recorder initialized");
	}

	private void prepareRecorder() {
		recorder.setPreviewDisplay(holder.getSurface());

		try {
			recorder.prepare();
		} catch (IllegalStateException e) {
			e.printStackTrace();
			finish();
		} catch (IOException e) {
			e.printStackTrace();
			finish();
		}
	}

	public void onClick(View v) {
		if (recording) {
			recorder.stop();
			recording = false;

			// Let's initRecorder so we can record again
			initRecorder();
			prepareRecorder();
		} else {
			recording = true;
			recorder.start();
		}
	}

	public void surfaceCreated(SurfaceHolder holder) {
		prepareRecorder();
	}

	public void surfaceChanged(SurfaceHolder holder, int format, int width,
			int height) {
	}

	public void surfaceDestroyed(SurfaceHolder holder) {
		if (recording) {
			recorder.stop();
			recording = false;
		}
		recorder.release();
		finish();
	}
}
