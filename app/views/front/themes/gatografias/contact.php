<?php
  include "./Include/begin.php";
  include "./Include/header.php";
?>

<!-- TOP TITLE -->
<section class="topSingleBkg topPageBkg">
    <div class="inner-desc">
    <h1 class="post-title single-post-title">Contacto</h1>
    </div>
</section>
<!-- /TOP TITLE -->
<!-- CONTENT SECTION --> 
<section id="wrap-content" class="page-content">
    <div class="container">
    <div class="row">
        <div class="col-md-12">
        <div class="page-holder custom-page-template-fw">
            <p>Si tienes preguntas o necesitas consultar algo, por favor no dudes en contactarnos.</p>
            <div id="contact-form-holder">
            <form method="post" id="contact-form" action='./Include/contact-process.php'>
                <div class="row">
                <div class="col-sm-4"><input type="text" name="name" value="" class="comm-field" placeholder="Nombre" /> </div>
                <div class="col-sm-4"><input type="text" name="email" value="" class="comm-field" placeholder="Email" /> </div>
                <div class="col-sm-4"><input type="text" name="subject" value="" class="comm-field" placeholder="Asunto" /></div>
                </div>
                <p><textarea name="message" rows="5" class="" id="msg-contact" placeholder="Mensaje"></textarea></p>
                <p class="antispam">Leave this empty: <input type="text" name="url" /></p>
                <p class="contact-btn"><input type="submit" value="Enviar" id="submit"/></p>
            </form>
            </div>
            <!-- contact-form-holder-->
            <!-- <div id="output-contact"></div> -->
        </div>
        <!--page-holder-->
        </div>
        <!--col-md-12-->
    </div>
    <!--row-->
    </div>
    <!--container-->
</section>
<!-- /CONTENT SECTION --> 

<?php
  include "./Include/footer.php";
  include "./Include/scripts.php";
  include "./Include/contact-scripts.php";
  include "./Include/end.php";
?>