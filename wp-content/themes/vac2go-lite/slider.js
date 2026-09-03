(function(){
  function init(){
    var w=document.querySelector('.et_pb_fullwidth_slider_0'); if(!w) return;
    var slides=[].slice.call(w.querySelectorAll('.et_pb_slide')); if(slides.length<2) return;
    if(window.matchMedia('(max-width:767px)').matches) return; // mobile = static slide 0
    var dots=[].slice.call(w.querySelectorAll('.et-pb-controllers a'));
    slides.forEach(function(s){ s.classList.add('hw-fade'); });
    var i=0, timer=null;
    function show(n){ slides.forEach(function(s,k){ if(k===n){s.style.position='relative';s.style.opacity='1';} else {s.style.position='absolute';s.style.opacity='0';} }); if(dots.length)dots.forEach(function(d,k){d.classList.toggle('et-pb-active-control',k===n);}); i=n; }
    function next(){ show((i+1)%slides.length); }
    function start(){ stop(); timer=setInterval(next,6000); }
    function stop(){ if(timer){clearInterval(timer);timer=null;} }
    dots.forEach(function(d,k){ d.addEventListener('click',function(e){e.preventDefault();show(k);start();}); });
    start();
    w.addEventListener('mouseenter',stop); w.addEventListener('mouseleave',start);
  }
  if(document.readyState!=='loading') init(); else document.addEventListener('DOMContentLoaded',init);
})();
