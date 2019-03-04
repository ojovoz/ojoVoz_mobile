ojoVoz webServer
----------------
2003-2016, Eugenio Tisselli

----------------

1. Requirements:
- Apache web server
- PHP 5.3.8
- MySQL 5.1.54
- ffmpeg (http://ffmpeg.org/) with MP3 codecs (--enable-libmp3lame)

----------------

2. Installation:

2.1. Create an empty MySQL database on your server. Add user with SELECT, INSERT, UPDATE and DELETE privileges

2.2. Import the ovwebserver.sql command file into your empty database (found in the 'database' folder)

2.3. Modify includes/init_database.php, and enter the following values: $db (name of the new database), $db_user and $db_pass

2.4. Modify includes/channel_vars.php, and configure the following basic variables:

-- $global_channel_name: name of your new ojoVoz project

-- $channel_folder: name of the folder where you will install your ojoVoz project. All scripts will be subfolders of that folder.

-- $mail_server: address of your mail server. In the example string, port 110 is used, with protocol POP3 and NOTLS parameter. This string is used to retrieve incoming messages.

-- $mapbox_api_key and $mapbox_id: Your Mapbox ID and API key (https://www.mapbox.com)

-- $servpath: the full path to your web server (where web documents are located)

-- $ffmpeg_path: full path to ffmpeg in your server

-- $smtp_server: address of your SMTP server. This is used to tell the ojoVoz mobile app which SMTP server it should connect to

-- $smtp_server_port: SMTP port of your server. Usually 578.

You can modify other variables, names are self-explanatory.

2.5. Upload all folders to your server. You should upload them as subfolders of the folder defined in $channel_folder (see above)

2.6. Grant read, write and execute permissions to 'channels' folder and all its subfolders.

----------------

3. Creating channels
Channels represent mobile users. There should be one channel per mobile phone.

3.1. Go to yourserver.net/$channel_folder/control (we suggest that you protect this folder with a password)

3.2. Go to 'Channels' On the top of this page, you will see two channels: '01' (a first test channel) and 'General', which acts as a container for all channels.

3.3. Edit channel '01'. Modify the channel's email address (ovw01xx@myserver.net by default) to a mail address on your server (you will need to create it manually). Please note that this address will be used to retrieve messages coming from the phone. You shouldn't use this address for other purposes. On the next field, enter the password for this address. The field 'phone id' refers to the ID of the phone associated to this channel. The field 'tag list' is used to specify the tag list that will appear on the phone associated to this channel. Use ';' to separate tags. Hit 'Edit' at the bottom of the page.

3.4. To create a new channel, you should go to yourserver.net/$channel_folder/control, choose 'Channels' and enter the required data to create a new channel. Please note that each channel must use a separate email address. Channel folder names should be unique. After the channel has been created by hitting the 'Add' button, you should include it in the 'General' container. To do this, edit the 'General' channel. Scroll down and choose the 'group' option that appears next to the 'Crono' checkbox. Then, add the your new channel by choosing it from the drop-down list.

----------------

4. Sending a message from a phone

4.1. Install the ojoVoz mobile app on your Android phone. When you first run the app, it will ask for a phone ID and a web server. Enter the phone ID of the channel to which your phone will be associated, and the web server of your ojoVoz project (http://yourserver.net/$channel_folder) If the app is already installed and you wish to modify this information, please follow steps 4.2. and 4.3., otherwise skip to 4.4.

4.2. On the app, press the 'menu' key and choose 'my name'

4.3. Enter 'admin'. You will then be asked to enter the phone's ID and the web server.

4.4. Exit the ojoVoz app and restart it so that changes will take effect.
