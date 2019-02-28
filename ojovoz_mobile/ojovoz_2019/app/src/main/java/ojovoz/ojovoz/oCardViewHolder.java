package ojovoz.ojovoz;

import android.support.v7.widget.CardView;
import android.support.v7.widget.RecyclerView;
import android.view.View;
import android.widget.CheckBox;
import android.widget.ImageView;
import android.widget.TextView;

/**
 * Created by Eugenio on 25/04/2018.
 */
public class oCardViewHolder extends RecyclerView.ViewHolder {

    CardView cv;
    CheckBox cb;
    TextView info;
    ImageView image;

    oCardViewHolder(View itemView) {
        super(itemView);
        cv = (CardView) itemView.findViewById(R.id.cardView);
        cb = (CheckBox) itemView.findViewById(R.id.checkbox);
        cb.setButtonDrawable(R.drawable.custom_checkbox);
        cb.setPadding(4, 4, 4, 4);
        cb.setChecked(true);
        info = (TextView) itemView.findViewById(R.id.info);
        info.setTextSize(16f);
        image = (ImageView) itemView.findViewById(R.id.imageView);
    }

}
