/* click2zap
 * http://mrclay.org/index.php/2006/02/18/click2zap-bookmarklet/
 * v1.0 alpha
 */
(function(){
  var i=0,l,d=document,b=d.body,w=window;
  w.cZ=1;
  w.cZa=[];
  while(l=b.getElementsByTagName('*').item(i++)){
    l.onmouseover=function(e){
      if(!e)
        var e=w.event;
      e.cancelBubble=true;
      if(e.stopPropagation)
        e.stopPropagation();
      if(w.cZ){
        this.style.background='yellow';
      }
    };
    l.onmouseout=function(e){
      if(!e)
        var e=w.event;
      e.cancelBubble=true;
      if(e.stopPropagation)
        e.stopPropagation();
      if(w.cZ){
        this.style.background='';
      }
    };
    l.onclick=function(e){
      if(!e)
        var e=w.event;
      e.cancelBubble=true;
      if(e.stopPropagation)
        e.stopPropagation();
      if(!w.cZ){
        return true;
      }
      this.style.background='';
      var h=d.createTextNode('');
      w.cZa.push(this,this.parentNode,h);
      this.parentNode.replaceChild(h,this);
      return false;
    };
  }
  var c=d.createElement('table');
  c.innerHTML='click2zap: <'+'u id=cZp>print<'+'/u> | <'+'u id=cZt>disable<'+'/u> | <'+'u id=cZu>undo<'+'/u>';
  c.style.background='red';
  c.style.color='#fff';
  c.style.position='fixed';
  c.style.top='0';
  c.style.right='0';
  b.insertBefore(c,b.firstChild);
  c.onclick=function(e){
    if(!e)
      var e=w.event;
    e.cancelBubble=true;
    if(e.stopPropagation)
      e.stopPropagation();
  };
  d.getElementById('cZp').onclick=function(e){
    b.removeChild(c);
    w.print();
  };
  d.getElementById('cZt').onclick=function(e){
    w.cZ=w.cZ?0:1;
    this.innerHTML=w.cZ?'disable':'enable';
  };
  d.getElementById('cZu').onclick=function(e){
    if(!w.cZa.length)
      return;
    var h=w.cZa.pop(),p=w.cZa.pop(),r=w.cZa.pop();
    p.replaceChild(r,h);
  };
}
)();