package ojovoz.ojovoz;

import android.os.Handler;

/**
 * Created by Eugenio on 20/02/2019.
 */
public class locationTimer {
    private int _interval;
    public int getInterval() { return _interval; }
    public void setInterval(int delay) { _interval = delay; }
    private Handler handler;
    private Runnable _tickHandler;
    private Runnable delegate;
    private boolean ticking;
    public boolean getIsTicking(){ return ticking; }

    public locationTimer(int interval)
    {
        _interval = interval;
        handler = new Handler();
    }

    public locationTimer(int interval, Runnable onTickHandler)
    {
        _interval = interval;
        setOnTickHandler(onTickHandler);
        handler = new Handler();
    }

    public void start(int interval, Runnable onTickHandler)
    {
        if (ticking) return;
        _interval = interval;
        setOnTickHandler(onTickHandler);
        handler.postDelayed(delegate, _interval);
        ticking = true;
    }

    public void start()
    {
        if (ticking) return;
        handler.postDelayed(delegate, _interval);
        ticking = true;
    }

    public void stop()
    {
        handler.removeCallbacks(delegate);
        ticking = false;
    }

    public void setOnTickHandler(Runnable onTickHandler)
    {
        if (onTickHandler == null)
            return;

        _tickHandler = onTickHandler;

        delegate = new Runnable() {
            public void run()
            {
                if (_tickHandler == null) return;
                _tickHandler.run();
                handler.postDelayed(delegate, _interval);
            }
        };
    }
}
