class button{
  int px;
  int py;
  int w;
  int h;
  String txt;
  int txtXOffset;
  int txtYOffset;
  
  button(int rx, int ry, int rw, int rh, String rTxt, int rTxtX, int rTxtY) {
    px=rx;
    py=ry;
    w=rw;
    h=rh;
    txt=rTxt;
    txtXOffset=rTxtX;
    txtYOffset=rTxtY;
  }
  
  boolean isMouseOver(int mx, int my) {
    if(mx>=px && mx<=(px+w) && my>=py && my<=(py+h)) {
      return true;
    } else {
      return false;
    }
  }
  
  
  void drawButton(int mx, int my) {
    int col=0;
    if(isMouseOver(mx,my)) {
      col=100;
    } 
    fill(col);
    rect(px,py,w,h);
    fill(255);
    if(txt!="up" && txt!="down"){
      text(txt,px+txtXOffset,py+txtYOffset);
    } else if(txt=="up") {
      line(px+10,py+30,px+20,py+10);
      line(px+20,py+10,px+30,py+30);
    } else if(txt=="down"){
      line(px+10,py+10,px+20,py+30);
      line(px+20,py+30,px+30,py+10);
    }
  }
}