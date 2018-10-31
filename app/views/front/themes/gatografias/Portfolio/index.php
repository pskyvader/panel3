<?php
  include "../Include/begin.php";
  include "../Include/header.php";
?>

<!-- TOP TITLE -->
<section class="topSingleBkg topPageBkg ">
  <div class="item-img" ></div>
  <div class="inner-desc">
    <h1 class="post-title single-post-title">Portafolio</h1>
    <span class="post-subtitle"> Mis proyectos actuales</span>
  </div>
</section>
<!-- /TOP TITLE -->
<!-- PORTFOLIO SECTION --> 
<section class="layout-portfolio layout-full">
<div class="container">
  <div class="portfolio-zigzag clearfix">
    <div class="gallery-item-zigzag gallery-item-zigzag-left clearfix" >
      <a href="./Pets">
        <div class="zigzag-img-holder">
          <div class="gallery-item-zigzag-img" style="background-image:url('../Images/Portfolio/Pets.jpg');"></div>
        </div>
      </a>
      <div class="zigzag-desc-holder">
        <div class="gallery-item-zigzag-desc">
          <h2>Mascotas</h2>
          <div class="gallery-item-subtitle">Animales adorables</div>
          <a class="view-more" href="./Pets">Click para ver galer&iacute;a</a>
        </div>
      </div>
    </div>
    <!-- /gallery-item -->  
    <div class="gallery-item-zigzag gallery-item-zigzag-right clearfix">
      <a href="./Projects">
        <div class="zigzag-img-holder">
          <div class="gallery-item-zigzag-img" style="background-image:url('../Images/Portfolio/Projects.jpg');"></div>
        </div>
      </a>
      <div class="zigzag-desc-holder">
        <div class="gallery-item-zigzag-desc">
          <h2>Proyectos</h2>
          <div class="gallery-item-subtitle">Mis proyectos personales actuales</div>
          <a class="view-more" href="./Projects">Click para ver galer&iacute;a</a>
        </div>
      </div>
    </div>
    <!-- /gallery-item --> 
    <div class="gallery-item-zigzag gallery-item-zigzag-left clearfix">
      <a href="./Travels">
        <div class="zigzag-img-holder">
          <div class="gallery-item-zigzag-img" style="background-image:url('../Images/Portfolio/Travels.jpg');"></div>
        </div>
      </a>
      <div class="zigzag-desc-holder">
        <div class="gallery-item-zigzag-desc">
          <h2>Viajes</h2>
          <div class="gallery-item-subtitle">Mis aventuras alrededor del mundo</div>
          <a class="view-more" href="./Travels">Click para ver galer&iacute;a</a>
        </div>
      </div>
    </div>
    <!-- /gallery-item -->   
    <div class="gallery-item-zigzag gallery-item-zigzag-right clearfix">
      <a href="./Others">
        <div class="zigzag-img-holder">
          <div class="gallery-item-zigzag-img" style="background-image:url('../Images/Portfolio/Others.jpg');"></div>
        </div>
      </a>
      <div class="zigzag-desc-holder">
        <div class="gallery-item-zigzag-desc">
          <h2>Otros</h2>
          <div class="gallery-item-subtitle">Paisajes, Retratos, etc...</div>
          <a class="view-more" href="./Others">Click para ver galer&iacute;a</a>
        </div>
      </div>
    </div>
    <!-- /gallery-item -->   
  </div>
  <!-- portfolio-zig-zag -->
</div>
</section>
<!-- /PORTFOLIO SECTION --> 

<?php
  include "../Include/footer.php";
  include "../Include/scripts.php";
  include "../Include/end.php";
?>