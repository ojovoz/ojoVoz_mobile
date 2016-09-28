class calendar{
  ArrayList dates;
  ArrayList buttons;
  button topButton;
  int px;
  int py;
  dateElement currentDate;
  int currentTop;
  String[] months = {"","January","February","March","April","May","June","July","August","September","October","November","December"};
  int maxButtons=5;
  boolean dropDown=false;
  boolean recalcContents=false;
  button up;
  button down;
  
  calendar(int rpx, int rpy, String l){
    dates = new ArrayList();
    buttons = new ArrayList();
    currentTop = 0;
    px=rpx;
    py=rpy;
    topButton = new button(px,py,273,40,l,14,29);
    up = new button(px+273,py+40,40,40,"up",0,0);
    down = new button(px+273,0,40,40,"down",0,0);
  }
  
  void addDate(int rMonth, int rYear){
    if(!dateExists(rMonth, rYear)){
      String l = months[rMonth] + " " + str(rYear);
      dateElement dE = new dateElement(l,rMonth,rYear);
      dates.add(dE);
    
      button b = new button(px,0,topButton.w,40,l,14,29);
      buttons.add(b);
    }
  }
  
  boolean dateExists(int rMonth, int rYear){
    boolean ret = false;
    for(int i=0; i<dates.size(); i++){
      dateElement dE = (dateElement)dates.get(i);
      if(dE.month == rMonth && dE.year == rYear){
        ret = true;
        break;
      }
    }
    return ret;
  }
  
  void drawCalendar(int mx, int my){
    int m=0;
    int startY;
    topButton.drawButton(mx,my);
    if(dropDown){
      if(buttons.size()>(currentTop+maxButtons)){
        m=currentTop+maxButtons;
      } else {
        m=buttons.size();
      }
      startY = topButton.py + topButton.h;
      for(int i=currentTop;i<m;i++){
        button b = (button)buttons.get(i);
        b.py = startY;
        b.drawButton(mx,my);
        startY+=b.h;
      }
      if(currentTop>0){
        up.drawButton(mx,my);
      }
      if(m<buttons.size()){
        down.py=topButton.py + topButton.h+((maxButtons-1)*40);
        down.drawButton(mx,my);
      }
    }
  }
  
  boolean isMouseOver(int mx, int my){
    int m=0;
    boolean ret=false;
    
    ret = topButton.isMouseOver(mx,my);
    if(ret){
      dropDown = !dropDown;
      recalcContents = false;
    } else if(dropDown) {
      if(buttons.size()>(currentTop+maxButtons)){
        m=currentTop+maxButtons;
      } else {
        m=buttons.size();
      }
      for(int i=currentTop;i<m;i++){
        button b = (button)buttons.get(i);
        if(b.isMouseOver(mx,my)){
          currentDate=(dateElement)dates.get(i);
          dropDown = !dropDown;
          recalcContents = true;
          ret=true;
          topButton.txt=b.txt;
          break;
        }
      }
      if(!ret){
        if(up.isMouseOver(mx,my)){
          currentTop--;
          ret=true;
        } else if(down.isMouseOver(mx,my)){
          currentTop++;
          ret=true;
        }
      }
    }
  
    return ret;
  }
  
  int getCurrentMonth(){
    return currentDate.month;
  }
  
  int getCurrentYear(){
    return currentDate.year;
  }
  
  void sortDates(){
    boolean swapped=true;
    int j=0;
    dateElement dE1;
    dateElement dE2;
    button b1;
    button b2;
    while(swapped){
      swapped=false;
      j++;
      for(int i=0;i<dates.size()-j;i++){
        dE1 = (dateElement)dates.get(i);
        dE2 = (dateElement)dates.get(i+1);
        if((dE1.year < dE2.year) || ((dE1.year == dE2.year) && (dE1.month < dE2.month))){
          dates.remove(i);
          dates.add(i,dE2);
          dates.remove(i+1);
          dates.add(i+1,dE1);
          
          b1 = (button)buttons.get(i);
          b2 = (button)buttons.get(i+1);
          
          buttons.remove(i);
          buttons.add(i,b2);
          buttons.remove(i+1);
          buttons.add(i+1,b1);
          
          swapped = true;
        }
      }
    }
  }
}