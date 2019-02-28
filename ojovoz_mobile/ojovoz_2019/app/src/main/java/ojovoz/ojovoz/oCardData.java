package ojovoz.ojovoz;

import android.graphics.Color;

/**
 * Created by Eugenio on 25/04/2018.
 */
public class oCardData {
    public int line;
    public boolean isSelected;
    public int backColor;
    public int infoColor;
    public String info;
    public String imgFile;
    public String sndFile;

    oCardData(){
        isSelected=true;
        info="";
        infoColor= Color.BLACK;
        imgFile="";
        sndFile="";
    }
}
