<?php
  include "../../Include/begin.php";
  include "../../Include/header.php";
?>

<!-- TOP TITLE -->
<section class="topSingleBkg topPageBkg ">
<div class="item-img" ></div>
<div class="inner-desc">
  <h1 class="post-title single-post-title">Exeter</h1>
  <span class="post-subtitle">Marzo 2017</span>
</div>
</section>
<section id="wrap-content" class="page-content">
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <div class="page-holder custom-page-template">
        <div class="row">
          <div class="col-md-6">
            texto-aqui
          </div>
          <div class="col-md-6">
            texto-aqui
          </div>
        </div>
      </div>
    </div>
    <!--col-md-12-->
  </div> 
  <!--row-->
</div>
<!--container-->
</section>
<!-- /TOP TITLE -->
<!-- GALLERY SECTION --> 
<section class="gallery-container layout-full">
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <div class="gallery-holder">
        <div class="gal-item">
          <?php 
            if(@GetImageSize("../../Images/Portfolio/Travels/Exeter/exeter-1.jpg")){
                echo '<a href="../../Images/Portfolio/Travels/Exeter/exeter-1.jpg">';
            }
          ?>
          <img src="../../Images/Portfolio/Travels/Exeter/exeter-1_thumb.jpg" />      
          </a>
        </div>
        <!--gal-item-->
      </div>
      <!--gallery-holder-->
    </div>
    <!--col-md-12-->
  </div>
  <!--row-->
</div>
<!--container-->
</section>
<!-- /GALLERY SECTION --> 
  
<?php
  include "../../Include/footer.php";
  include "../../Include/scripts.php";
  include "../../Include/end.php";
?>