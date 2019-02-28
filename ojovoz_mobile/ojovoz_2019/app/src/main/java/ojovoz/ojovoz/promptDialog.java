package ojovoz.ojovoz;

import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.EditText;

/**
 * Created by Eugenio on 13/03/2017.
 */
public abstract class promptDialog extends AlertDialog.Builder implements DialogInterface.OnClickListener {
    private final EditText input;

    /**
     * @param context
     * @param title resource id
     * @param message resource id
     */
    public promptDialog(Context context, int title, int message, String user) {

        super(context);
        setTitle(title);
        setMessage(message);

        LayoutInflater inflater = (LayoutInflater) context.getSystemService( Context.LAYOUT_INFLATER_SERVICE );

        //input = new EditText(context);
        View view = inflater.inflate( R.layout.edit_text_template, null );
        input = (EditText)view.findViewById(R.id.myEditText);
        input.setSingleLine();
        input.setText(user);
        input.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                input.selectAll();
            }
        });
        setView(input);

        setPositiveButton(R.string.okButtonText, this);
        setNegativeButton(R.string.cancelButtonText, this);

    }

    /**
     * will be called when "cancel" pressed.
     * closes the dialog.
     * can be overridden.
     * @param dialog
     */
    public void onCancelClicked(DialogInterface dialog) {
        dialog.dismiss();
    }

    @Override
    public void onClick(DialogInterface dialog, int which) {
        if (which == DialogInterface.BUTTON_POSITIVE) {
            if (onOkClicked(input.getText().toString())) {
                dialog.dismiss();
            }
        } else {
            onCancelClicked(dialog);
        }
    }

    /**
     * called when "ok" pressed.
     * @param input
     * @return true, if the dialog should be closed. false, if not.
     */
    abstract public boolean onOkClicked(String input);


}