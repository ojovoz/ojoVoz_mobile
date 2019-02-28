import ddf.minim.*;
import ddf.minim.analysis.*;
import ddf.minim.effects.*;
import ddf.minim.signals.*;
import ddf.minim.spi.*;
import ddf.minim.ugens.*;

XML remote_xml = null;
XML local_xml = null;
String remote_xml_url;

boolean loadingRemote=false;
boolean loadingLocal=false;
boolean preLoadingRemote=false;
boolean preLoadingLocal=false;

ArrayList posts;
ArrayList currentPosts;
int postsStartY=75;
int smallPostWidth=100;

int largePostX=160;
int largePostY=postsStartY;
int largePostWidth=650;
int largePostHeight=487;
post showing=null;

AudioPlayer audioFile;
boolean audioPlaying=false;

button refresh;
button wait;
button playSound;

int currentMonth=-1;
int currentYear=-1;
calendar postCalendar;

PFont largeFont;
PFont smallFont;

void setup() {
  PImage titlebaricon = loadImage("ojoVoz.png");
  surface.setIcon(titlebaricon);
  surface.setTitle("Macho Sauti offilne viewer"); 
  
  String source[] = loadStrings("XML_source.txt");
  remote_xml_url = source[0];

  posts = new ArrayList();
  createPosts();
  prepareCurrentPosts();

  postCalendar = new calendar(largePostX, 20, "Choose date");
  addDatesToCalendar();

  refresh = new button(20, 20, 100, 40, "Refresh", 14, 29);
  wait = new button(largePostX, (height/2)-20, 350, 40, "Loading contents. Please wait...", 14, 29);
  playSound = new button(0,0,150,40,"Play audio",21,29);

  largeFont = loadFont("HPSimplified-Light-24.vlw");
  smallFont = loadFont("HPSimplified-Light-12.vlw");

  size(900, 650);
  smooth();
  stroke(255);
  noFill();
}

void draw() {
  background(0, 50, 0);

  if (loadingRemote) {
    readRemoteXML();
    loadingRemote=false;
  }

  if (loadingLocal) {
    prepareCurrentPosts();
    loadingLocal=false;
  }

  textFont(smallFont);

  drawPosts(mouseX, mouseY);
  movePosts(mouseX, mouseY);

  textFont(largeFont);

  if (showing!=null) {
    drawLargePost(showing);
    playSound.drawButton(mouseX,mouseY);
  }

  postCalendar.drawCalendar(mouseX, mouseY);

  stroke(0, 50, 0);
  fill(0, 50, 0);
  rect(0, 0, wait.px-1, 74);
  stroke(255);
  noFill();
  refresh.drawButton(mouseX, mouseY);

  if (preLoadingRemote) {
    wait.drawButton(0, 0);
    loadingRemote=true;
    preLoadingRemote=false;
  }

  if (preLoadingLocal) {
    wait.drawButton(0, 0);
    loadingLocal=true;
    preLoadingLocal=false;
  }
  
  if(audioPlaying){
    if (audioFile.position() == audioFile.length()){
      toggleSound(showing);
    }
  }
}

void mousePressed() {
  if (!loadingRemote && !loadingLocal && refresh.isMouseOver(mouseX, mouseY)) {
    preLoadingRemote=true;
    postCalendar.dropDown=false;
    if(audioPlaying) {
      toggleSound(showing);
    }
  } else if (!loadingRemote && !loadingLocal && postCalendar.isMouseOver(mouseX, mouseY)) {
    if (postCalendar.recalcContents) {
      currentMonth=postCalendar.getCurrentMonth();
      currentYear=postCalendar.getCurrentYear();
      postCalendar.recalcContents=false;
      preLoadingLocal=true;
      if(audioPlaying) {
        toggleSound(showing);
      }
    }
  } else if (!loadingRemote && !loadingLocal && playSound.isMouseOver(mouseX, mouseY)) { 
    toggleSound(showing);
  } else {
    for (int i=0; i<currentPosts.size(); i++) {
      post p = (post)currentPosts.get(i);
      if (p.isMouseOver(mouseX, mouseY)) {
        if(audioPlaying) {
          toggleSound(showing);
        }
        showing = p;
        postCalendar.dropDown=false;
        break;
      }
    }
  }
}

void createPosts() {
  try {
    local_xml = loadXML("local_XML.xml");
    XML[] children = local_xml.getChildren("item");

    for (int i = 0; i < children.length; i++) {
      String title = children[i].getContent();
      int id = children[i].getInt("id");
      String date = children[i].getString("date");
      String image = children[i].getString("image");
      String audio = children[i].getString("audio");
      int month = children[i].getInt("month");
      int year = children[i].getInt("year");
      addItemToPosts(id, title, date, image, audio, month, year);
      if (year>currentYear) {
        currentYear=year;
        currentMonth=month;
      } else if (month>currentMonth) {
        currentMonth=month;
      }
    }
  } 
  catch(Exception e) {
    String data = "<channel></channel>";
    local_xml = parseXML(data);
  }
}

void addItemToPosts(int id, String title, String date, String image, String audio, int month, int year) {
  post p = new post(id, null, image, audio, 0, 0, 0, 0, date + "\n" + title, 0, 0, month, year);
  posts.add(p);
}

void readRemoteXML() {
  boolean calcDate=false;

  if (currentMonth==-1 && currentYear==-1) {
    calcDate=true;
  } 

  try {
    remote_xml = loadXML(remote_xml_url);
    XML[] children = remote_xml.getChildren("item");

    for (int i = 0; i < children.length; i++) {
      String title = children[i].getContent();
      int id = children[i].getInt("id");
      String date = children[i].getString("date");
      String image = children[i].getString("image");
      String audio = children[i].getString("audio");
      int month = children[i].getInt("month");
      int year = children[i].getInt("year");

      if (!itemExistsLocally(id)) {
        if (getRemoteContents(id, image, audio)) {
          addItemToPosts(id, title, date, "image"+id+".jpg", "audio"+id+".mp3", month, year);
          addItemToLocalXML(id, title, date, month, year);
          if (calcDate) {
            if (year>currentYear) {
              currentYear=year;
              currentMonth=month;
            } else if (month>currentMonth) {
              currentMonth=month;
            }
          }
        }
      }
    }
    prepareCurrentPosts();

    postCalendar = null;
    postCalendar = new calendar(largePostX, 20, "Choose date");
    addDatesToCalendar();
  } 
  catch (Exception e) {
  }
  loadingRemote=false;
}

boolean getRemoteContents(int rId, String rI, String rA) {
  PImage i;
  boolean ret=false;
  int iWT, iHT = 0;

  i = loadImage(rI, "jpg");
  int iW = i.width;
  int iH = i.height;
  if (iW>0) {
    
    if (iW>iH) {
      iWT=largePostWidth;
      iHT=0;
    } else {
      iHT=largePostHeight;
      iWT=0;
    }
    
    PImage newImage = createImage(iW, iH, RGB);
    newImage = i.get();
    newImage.resize(iWT,iHT);
    newImage.save("data/content/image"+rId+".jpg");

    byte b[] = loadBytes(rA);
    if (b.length>0) {
      saveBytes("data/content/audio"+rId+".mp3", b);
      ret = true;
    }
  } 

  return ret;
}

void addItemToLocalXML(int rId, String rTitle, String rDate, int rMonth, int rYear) {
  XML newItem = local_xml.addChild("item");
  newItem.setInt("id", rId);
  newItem.setContent(rTitle);
  newItem.setString("date", rDate);
  newItem.setString("image", "image"+rId+".jpg");
  newItem.setString("audio", "audio"+rId+".mp3");
  newItem.setString("month", str(rMonth));
  newItem.setString("year", str(rYear));
  saveXML(local_xml, "data/local_XML.xml");
}

boolean itemExistsLocally(int id) {
  boolean ret=false;
  for (int i=0; i<posts.size(); i++) {
    post p = (post)posts.get(i);
    if (p.id == id) {
      ret=true;
      break;
    }
  }
  return ret;
}

void prepareCurrentPosts() {
  int tW, tH=0;
  float n;

  postsStartY=75;
  currentPosts=new ArrayList();
  for (int i=0; i<posts.size(); i++) {
    post p = (post)posts.get(i);
    if (p.month==currentMonth && p.year==currentYear) {
      p.px=20;
      p.py=postsStartY;
      if (p.image==null) {
        PImage img = loadImage("data/content/" + p.imageFilename, "jpg");
        p.image=img;
      }
      int iW = p.image.width;
      int iH = p.image.height;
      if (iW>smallPostWidth) {
        n = (float)smallPostWidth/iW;
        tW = smallPostWidth;
        tH = (int)(iH*n);
      } else {
        tW = iW;
        tH = iH;
      }
      p.w=tW;
      p.h=tH;
      p.txtYOffset=tH+15;
      currentPosts.add(p);
      postsStartY+=(tH+36);
    }
  }
}

void drawPosts(int mx, int my) {
  for (int i=0; i<currentPosts.size(); i++) {
    post p = (post)currentPosts.get(i);
    p.drawPostMin(mx, my);
  }
}

void movePosts(int mx, int my) {
  float f;
  if (currentPosts.size()>0) {
    post first = (post)currentPosts.get(0);
    post last = (post)currentPosts.get(currentPosts.size()-1);
    if (mx<=smallPostWidth+20 && my>=75 && (my<=((height/2)-70) || my>=((height/2)+70))) {

      float a = ((height-75)/2)-(float)(my-75);
      f = a/((height-75)/2);
      f *= 5;

      if ((f>0 && first.py<75) || (f<0 && (last.py+last.h)>(height-35))) {
        for (int i=0; i<currentPosts.size(); i++) {
          post p = (post)currentPosts.get(i);
          p.py+=f;
        }
      }
    }
  }
}

void drawLargePost(post p) {
  int tW, tH;
  float n;
  PImage i = p.image;

  int iW = i.width;
  int iH = i.height;
  
  println(iW);


  if (iW>largePostWidth) {
    if (iW>iH) {
      n = (float)largePostWidth/iW;
      tW = largePostWidth;
      tH = (int)(iH*n);
      playSound.px=largePostX+tW-playSound.w;
      playSound.py=largePostY+tH+10;
    } else {
      n = (float)largePostHeight/iH;
      tH = largePostHeight;
      tW = (int)(iW*n);
      playSound.px=largePostX+tW+20;
      playSound.py=largePostY+tH-40;
    }
  } else {
    tW = iW;
    tH = iH;
    if (iW>iH) {
      playSound.px=largePostX+tW-playSound.w;
      playSound.py=largePostY+tH+10;
    } else {
      playSound.px=largePostX+tW+20;
      playSound.py=largePostY+tH-40;
    }
  }
  println(tH);
  image(i, largePostX, largePostY, tW, tH);
  fill(255);
  text(p.txt, largePostX, largePostY+tH+30);
}

void addDatesToCalendar() {
  int m=-1;
  int y=-1;
  for (int i=0; i<posts.size(); i++) {
    post p = (post)posts.get(i);
    if (p.year != y) {
      postCalendar.addDate(p.month, p.year);
      y=p.year;
      m=p.month;
    } else if (p.month != m) {
      postCalendar.addDate(p.month, p.year);
      m=p.month;
    }
  }
  postCalendar.sortDates();
}

void toggleSound(post p){
  if(audioPlaying){
    audioFile.pause();
    audioFile.rewind();
    audioFile=null;
    playSound.txt="Play audio";
  } else {
    Minim minim = new Minim(this);
    audioFile=minim.loadFile("content/"+p.audio,2048);
    audioFile.play();
    //audioFile.rate(0.5);
    //audioFile.amp(1.0);
    playSound.txt="Stop audio";
  }
  audioPlaying=!audioPlaying;
}