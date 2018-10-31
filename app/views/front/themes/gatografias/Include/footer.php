<?php
  // use this instagram access token generator http://instagram.pixelunion.net/
  $access_token="145682138.1677ed0.69ba31a2025b417aa611ae37d5e68fab";
  $photo_count=8;
     
  $json_link="https://api.instagram.com/v1/users/self/media/recent/?";
  $json_link.="access_token={$access_token}&count={$photo_count}";
?>

<!-- FOOTER INSTAGRAM --> 
    <section id="footer-instagram">
      <div class="widget-footer-instagram">
        <h5 class="widgettitle">Instagram</h5>
        <ul class="instagram-pics instagram-size-small">
          <?php
            $json = file_get_contents($json_link);
            $obj = json_decode($json, true, 512, JSON_BIGINT_AS_STRING);
            
            foreach ($obj['data'] as $post) {
                 
                $pic_text=$post['caption']['text'];
                $pic_link=$post['link'];
                $pic_like_count=$post['likes']['count'];
                $pic_comment_count=$post['comments']['count'];
                $pic_src=str_replace("http://", "https://", $post['images']['standard_resolution']['url']);
                $pic_created_time=date("F j, Y", $post['caption']['created_time']);
                $pic_created_time=date("F j, Y", strtotime($pic_created_time . " +1 days"));
                        
                echo "<li>";    
                  echo "<a href='{$pic_link}' target='_blank'>";
                    echo "<img src='{$pic_src}' alt='{$pic_text}'>";
                  echo "</a>";
                echo "</li>";
            }
          ?>
        </ul>
        <p class="clear"><a href="https://www.instagram.com/gatografiasdepora/" rel="me" target="_blank">S&iacute;gueme!</a></p>
      </div>
    </section>
    <!-- /FOOTER INSTAGRAM -->
    <!-- FOOTER -->
    <footer>
      <div class="container">
        <div class="footer-widgets">
          <div class="row">
            <!-- FOOTER COLUMN 1 -->
            <div class="col-md-4">
              <div class="foo-block">
                <div id="text-2" class="widget widget-footer widget_text">
                  <h5 class="widgettitle">Acerca de mi</h5>
                  <div class="textwidget">Amante de los gatos y la fotograf&iacute;a, disfruto admirando paisajes en mis viajes, tomando caf&eacute; en cualquier hora del d&iacute;a y jugando un buen videojuego.</div>
                </div>
              </div>
              <!--foo-block-->
            </div>
            <!--col-md-4-->
            <!-- FOOTER COLUMN 2 -->
            <div class="col-md-4">
              <div class="foo-block">
                <div id="text-3" class="widget widget-footer widget_text">
                  <h5 class="widgettitle">Cont&aacute;ctame</h5>
                  <div class="textwidget">
                    <p>
                      +56 9 9895 0866<br />
                      Paula Lagos Eyzaguirre<br />
                      <a href="mailto:pora@gatografias.cl">pora@gatografias.cl</a>
                    </p>
                  </div>
                </div>
              </div>
              <!--foo-block-->
            </div>
            <!--col-md-4-->
            <!-- FOOTER COLUMN 3 -->
            <div class="col-md-4">
              <div class="foo-block foo-last">
                <div id="recent-posts-3" class="widget widget-footer widget_recent_entries">
                  <h5 class="widgettitle">Art&iacute;culos Recientes</h5>
                  <ul>
                    <li><a href="#">No hay art&iacute;culos que mostrar</a></li>
                    <!-- <li><a href="blog-single-post">Dreaming of Summer</a></li>
                    <li><a href="blog-single-post">My Favorite Cities</a></li>
                    <li><a href="blog-single-post">Take Me Away</a></li> -->
                  </ul>
                </div>
              </div>
              <!--foo-block-->
            </div>
            <!--col-md-4-->
          </div>
          <!--row-->
        </div>
        <!--footer-widgets-->
        <!-- FOOTER SOCIAL -->    
        <ul class="footer-social">
          <li><a class="social-facebook" href="https://www.facebook.com/gatografiasdepora" target="_blank"><i class="fa fa-facebook"></i></a></li>
          <li><a class="social-instagram" href="https://www.instagram.com/gatografiasdepora/" target="_blank"><i class="fa fa-instagram"></i></a></li>
          <li><a class="social-flickr" href="https://www.flickr.com/photos/natashastark/" target="_blank"><i class="fa fa-flickr"></i></a></li>
          <li><a class="social-500px" href="https://500px.com/natashastark" target="_blank"><i class="fa fa-500px"></i></a></li>
          <li><a class="social-twitter" href="https://twitter.com/gatografias" target="_blank"><i class="fa fa-twitter"></i></a></li>
        </ul>
        <!-- FOOTER COPYRIGHT --> 
        <div class="copyright">
          <p>Copyright &copy; 2017, Luminis. Designed by MatchThemes</p>
        </div>
      </div>
      <!--container-->
    </footer>
    <!-- /FOOTER -->