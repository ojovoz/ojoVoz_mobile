package ojovoz.ojovoz;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.TimeZone;

/**
 * Created by Eugenio on 17/04/2018.
 */
public class dateHelper {

    dateHelper(){

    }

    public Date stringToDate(String d) {
        Date date = new Date();
        SimpleDateFormat sdf = new SimpleDateFormat("dd-MM-yyyy HH:mm:ss");
        sdf.setTimeZone(TimeZone.getDefault());
        try {
            date = sdf.parse(d);
        } catch (ParseException e) {

        }
        return date;
    }

    public String dateToString(Date d) {
        SimpleDateFormat sdf = new SimpleDateFormat("dd-MM-yyyy HH:mm:ss");
        sdf.setTimeZone(TimeZone.getDefault());
        return sdf.format(d);
    }

    public String dateToStringSend(Date d) {
        SimpleDateFormat sdf = new SimpleDateFormat("dd_MM_yyyy_kk_mm_ss");
        sdf.setTimeZone(TimeZone.getDefault());
        return sdf.format(d);
    }
}
