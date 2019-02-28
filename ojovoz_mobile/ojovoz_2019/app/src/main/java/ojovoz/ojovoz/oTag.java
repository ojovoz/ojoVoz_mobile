package ojovoz.ojovoz;

import android.content.Context;

import java.util.ArrayList;
import java.util.Collections;
import java.util.Comparator;
import java.util.Iterator;
import java.util.List;

/**
 * Created by Eugenio on 26/02/2019.
 */
public class oTag {

    public String tag;
    public int line;

    public Context context;

    oTag(){

    }

    oTag(Context c){
        context=c;
    }

    public ArrayList<oTag> createTagList(){
        ArrayList<oTag> ret = new ArrayList<>();
        csvFileManager tags;

        tags = new csvFileManager("tags");
        List<String[]> tagCSV = tags.read(context);
        if(tagCSV!=null) {
            Iterator<String[]> iterator = tagCSV.iterator();
            int n=0;
            while (iterator.hasNext()) {
                String[] record = iterator.next();
                oTag t = new oTag();
                t.line=n;
                t.tag = record[0];

                ret.add(t);

                n++;
            }
        }
        return ret;
    }

    public ArrayList<String> getTagNames(ArrayList<oTag> tagList){
        ArrayList<String> ret = new ArrayList<>();

        Iterator<oTag> iterator = tagList.iterator();
        while(iterator.hasNext()){
            oTag t = iterator.next();
            ret.add(t.tag);
        }
        return ret;
    }

    public void appendTag(ArrayList<oTag> allTags, String rTag){

        csvFileManager tags = new csvFileManager("tags");
        String[] newTags = rTag.split(",");

        for(int i=0; i<newTags.length; i++){

            boolean bProceed = true;
            Iterator<oTag> iterator = allTags.iterator();
            while(iterator.hasNext()){
                oTag t = iterator.next();
                if(t.tag.trim().toLowerCase().equals(newTags[i].trim().toLowerCase())){
                    bProceed = false;
                    break;
                }
            }

            if(bProceed) {
                String[] newLine = {newTags[i].trim().toLowerCase()};
                tags.append(context, newLine);
            }
        }

    }

    public ArrayList<oTag> sortTags(ArrayList<oTag> sortedTags, boolean reverse, int limit){
        Collections.sort(sortedTags, new Comparator<oTag>() {
            @Override
            public int compare(oTag t1, oTag t2) {
                return t1.tag.compareTo(t2.tag);
            }
        });

        if(reverse){
            Collections.reverse(sortedTags);
        }

        if(limit>0 && limit<sortedTags.size()){
            sortedTags.subList(0,limit);
        }

        return sortedTags;
    }
}
