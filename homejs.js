window.addEventListener("scroll", function() {
    let image = document.getElementById("scrollImage");
    let imagePosition = image.getBoundingClientRect().top;
    let windowHeight = window.innerHeight;
  
    // إذا كانت الصورة في نطاق الشاشة
    if (imagePosition < windowHeight) {
      image.style.display = "block";  // ظهور الصورة
    } else {
      image.style.display = "none";  // إخفاء الصورة
    }
  });
  