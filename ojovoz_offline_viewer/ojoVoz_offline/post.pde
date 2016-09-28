class post {
  int id;
  PImage image;
  String imageFilename;
  String audio;
  int px;
  float py;
  int w;
  int h;
  String txt;
  int txtXOffset;
  int txtYOffset;
  int month;
  int year;
  
  post(int rId, PImage rImage, String rImageFilename, String rAudio, int rPx, int rPy, int rW, int rH, String rTxt, int rTxtXOffset, int rTxtYOffset, int rMonth, int rYear) {
    id = rId;
    image = rImage;
    imageFilename = rImageFilename;
    audio = rAudio;
    px = rPx;
    py = rPy;
    w = rW;
    h = rH;
    txt = rTxt;
    txtXOffset = rTxtXOffset;
    txtYOffset = rTxtYOffset;
    month = rMonth;
    year = rYear;
  }
  
  boolean isMouseOver(int mx, int my) {
    if(mx>=px && mx<=(px+w) && my>=py && my<=(py+h)) {
      return true;
    } else {
      return false;
    }
  }
  
  void drawPostMin(int mx, int my) {
    if(isMouseOver(mx,my)){
      fill(100);
      rect(px-2,py-2,w+2,h+2);
    }
    image(image,px,py,w,h);
    fill(255);
    text(txt,px+txtXOffset,py+txtYOffset);
  }
}