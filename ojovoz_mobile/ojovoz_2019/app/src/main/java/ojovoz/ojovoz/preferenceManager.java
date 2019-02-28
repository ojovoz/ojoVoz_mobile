package ojovoz.ojovoz;

import android.content.Context;
import android.content.SharedPreferences;

import java.util.ArrayList;
import java.util.Iterator;

/**
 * Created by Eugenio on 08/03/2018.
 */
public class preferenceManager {

    Context context;

    preferenceManager(Context c){
        context=c;
    }

    public String getPreference(String keyName) {
        String value = "";
        SharedPreferences ugunduziPrefs = context.getSharedPreferences("ugunduziPrefs", Context.MODE_PRIVATE);
        value = ugunduziPrefs.getString(keyName, "");
        return value;
    }

    public int getPreferenceInt(String keyName) {
        int value = -1;
        SharedPreferences ugunduziPrefs = context.getSharedPreferences("ugunduziPrefs", Context.MODE_PRIVATE);
        value = ugunduziPrefs.getInt(keyName, -1);
        return value;
    }

    public boolean preferenceExists(String keyName){
        boolean ret=false;
        SharedPreferences ugunduziPrefs = context.getSharedPreferences("ugunduziPrefs", Context.MODE_PRIVATE);
        ret = ugunduziPrefs.contains(keyName);
        return ret;
    }

    public boolean getPreferenceBoolean(String keyName) {
        boolean value;
        SharedPreferences ugunduziPrefs = context.getSharedPreferences("ugunduziPrefs", Context.MODE_PRIVATE);
        value = ugunduziPrefs.getBoolean(keyName, false);
        return value;
    }

    public ArrayList<String> getPreferenceAsArrayList(String keyName, String separator, String prefixExcluded) {
        ArrayList<String> ret = new ArrayList<>();
        String list = getPreference(keyName);
        if(!list.isEmpty()) {
            String valuesArray[] = list.split(separator);
            for (int i = 0; i < valuesArray.length; i++) {
                if (!prefixExcluded.isEmpty()) {
                    if (prefixExcluded.charAt(0) != valuesArray[i].charAt(0)) {
                        ret.add(valuesArray[i]);
                    }
                } else {
                    ret.add(valuesArray[i]);
                }
            }
        }
        return ret;
    }

    public void savePreference(String keyName, String keyValue) {
        SharedPreferences ugunduziPrefs = context.getSharedPreferences("ugunduziPrefs", Context.MODE_PRIVATE);
        SharedPreferences.Editor prefEditor = ugunduziPrefs.edit();
        prefEditor.putString(keyName, keyValue);
        prefEditor.apply();
    }

    public void savePreferenceBoolean(String keyName, boolean keyValue){
        SharedPreferences ugunduziPrefs = context.getSharedPreferences("ugunduziPrefs", Context.MODE_PRIVATE);
        SharedPreferences.Editor prefEditor = ugunduziPrefs.edit();
        prefEditor.putBoolean(keyName, keyValue);
        prefEditor.apply();
    }

    public void savePreferenceInt(String keyName, int keyValue){
        SharedPreferences ugunduziPrefs = context.getSharedPreferences("ugunduziPrefs", Context.MODE_PRIVATE);
        SharedPreferences.Editor prefEditor = ugunduziPrefs.edit();
        prefEditor.putInt(keyName, keyValue);
        prefEditor.apply();
    }

    public void deletePreference(String keyName){
        SharedPreferences ugunduziPrefs = context.getSharedPreferences("ugunduziPrefs", Context.MODE_PRIVATE);
        SharedPreferences.Editor prefEditor = ugunduziPrefs.edit();
        prefEditor.remove(keyName);
        prefEditor.apply();
    }

    public ArrayList<String> getFarmsPendingSave(String keyName, String separator){
        ArrayList<String> ret = new ArrayList<>();
        ArrayList<String> current = getPreferenceAsArrayList(keyName, separator, "");
        Iterator<String> farmIterator = current.iterator();
        while (farmIterator.hasNext()) {
            String farmName = farmIterator.next();
            if(farmName.startsWith("*")){
                ret.add(farmName.substring(1));
            }
        }
        return ret;
    }

    public void updateSavedFarm(String updateFarmName, String keyName, String separator){
        String farms="";
        ArrayList<String> current = getPreferenceAsArrayList(keyName, separator, "");
        Iterator<String> farmIterator = current.iterator();
        while (farmIterator.hasNext()) {
            String farmName = farmIterator.next();
            if(farmName.startsWith("*") && updateFarmName.equals(farmName.substring(1))){
                farms = (farms.isEmpty()) ? updateFarmName : farms + separator + updateFarmName;
            } else {
                farms = (farms.isEmpty()) ? farmName : farms + separator + farmName;
            }
        }
        savePreference(keyName, farms);
    }

    public String getFarmsPendingDelete(String keyName, String separator){
        String farms="";
        String ret = "";
        ArrayList<String> current = getPreferenceAsArrayList(keyName, separator, "");
        Iterator<String> farmIterator = current.iterator();
        while (farmIterator.hasNext()) {
            String farmName = farmIterator.next();
            if(farmName.startsWith("-*")){
                //ignore
            } else {
                if (farmName.startsWith("-")) {
                    ret = (ret.isEmpty()) ? farmName.substring(1) : ret + separator + farmName.substring(1);
                }
                farms = (farms.isEmpty()) ? farmName : farms + separator + farmName;
            }
        }
        savePreference(keyName, farms);
        return ret;
    }

    public void updateDeletedFarms(String updateFarmNames, String keyName, String separator){
        String farms="";
        String[] updateFarmNamesList = updateFarmNames.split(separator);
        ArrayList<String> current = getPreferenceAsArrayList(keyName, separator, "");
        Iterator<String> farmIterator = current.iterator();
        while (farmIterator.hasNext()) {
            String farmName = farmIterator.next();
            String updateFarm="";
            for(int i=0; i<updateFarmNamesList.length;i++) {
                if (farmName.startsWith("-") && updateFarmNamesList[i].equals(farmName.substring(1))) {
                    updateFarm = updateFarmNamesList[i];
                    break;
                }
            }
            if(!updateFarm.isEmpty()){
                //ignore
            } else {
                farms = (farms.isEmpty()) ? farmName : farms + separator + farmName;
            }
        }
        savePreference(keyName, farms);
    }
}