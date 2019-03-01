package ojovoz.ojovoz;

import android.content.Context;

import java.util.ArrayList;
import java.util.Collections;
import java.util.Comparator;
import java.util.Date;
import java.util.Iterator;
import java.util.List;

/**
 * Created by Eugenio on 19/02/2019.
 */
public class oLog {

    public int line;
    public String user;
    public Date date;
    public String tags;
    public String latitude;
    public String longitude;
    public String pictureFile;
    public String soundFile;

    public Context context;

    private dateHelper dH;

    oLog(Context c){
        context = c;
        dH = new dateHelper();
    }

    oLog(){
        dH = new dateHelper();
    }

    public void appendToLog(String rUser, Date rDate, String rTags, String rLatitude, String rLongitude, String rPicture, String rSound){

        csvFileManager log = new csvFileManager("log");
        String[] newLine = {rUser, dH.dateToString(rDate), rTags, rLatitude, rLongitude, rPicture, rSound};

        log.append(context, newLine);
    }

    public ArrayList<oLog> createLog(){
        ArrayList<oLog> ret = new ArrayList<>();
        csvFileManager log;

        log = new csvFileManager("log");
        List<String[]> logCSV = log.read(context);
        if(logCSV!=null) {
            Iterator<String[]> iterator = logCSV.iterator();
            int n=0;
            while (iterator.hasNext()) {
                String[] record = iterator.next();
                oLog l = new oLog();
                l.line=n;
                l.user = record[0];
                l.date = dH.stringToDate(record[1]);
                l.tags = record[2];
                l.latitude = record[3];
                l.longitude = record[4];
                l.pictureFile = record[5];
                l.soundFile = record[6];

                ret.add(l);

                n++;
            }
        }
        return ret;
    }

    public ArrayList<oLog> sortLogByDate(ArrayList<oLog> sortedLog, boolean reverse, int limit){
        Collections.sort(sortedLog, new Comparator<oLog>() {
            @Override
            public int compare(oLog l1, oLog l2) {
                return l1.date.compareTo(l2.date);
            }
        });

        if(reverse){
            Collections.reverse(sortedLog);
        }

        if(limit>0 && limit<sortedLog.size()){
            sortedLog.subList(0,limit);
        }

        return sortedLog;
    }

    public void deleteLogItems(int[] delete){
        csvFileManager log = new csvFileManager("log");
        log.deleteLines(context, delete);
    }

    public String toString(String separator) {
        String ret = "";
        dateHelper dH = new dateHelper();
        String sTags = (tags.isEmpty()) ? "null" : tags;
        ret = user + separator + dH.dateToStringSend(date)  + separator + latitude + separator + longitude + separator + sTags;
        return ret;
    }

}
