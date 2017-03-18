package ojovoz;

/**
 * Created by Eugenio on 20/07/2016.
 */
public class soundPlayer {
    public int id;
    public boolean playing;
    public String filename;

    public soundPlayer() {

    }

    public void initialize(int n, String f) {
        id = n;
        playing = false;
        filename = f;
    }

    public void toggle() {
        playing = !playing;
    }

}
