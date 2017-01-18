<?php 
@$dbase = $_GET["dbase"];
?>
<script type="text/javascript" src="js/d3.v2.js"></script>
<script type="text/javascript" src="http://d3js.org/d3.v3.min.js"></script>
<script type='text/javascript'>
        // write vars used by the sunburst
        var reader_url = "<?php echo $config_readerURL;?>";
        var colors = ["<?php echo $config_colors[0];?>","<?php echo $config_colors[1];?>","<?php echo $config_colors[2];?>","<?php echo $config_colors[3];?>","<?php echo $config_colors[4];?>","<?php echo $config_colors[5];?>","<?php echo $config_colors[6];?>","<?php echo $config_colors[7];?>","<?php echo $config_colors[8];?>","<?php echo $config_colors[9];?>"];
        var groupmodels_url = "<?php echo $config_groupMoldelsURL;?>";
        var selfmodels_url = "<?php echo $config_selfMoldelsURL;?>";
        var json_file = "data/<?php echo $course;?>.json";
        var usr = "<?php echo $usr;?>";
        var grp = "<?php echo $grp;?>";
        var sid = "<?php echo $sid;?>";
        var course = "<?php echo $course;?>";
        var dbase = "<?php echo $dbase;?>";
        function getBookName(docsrc){
            var res = "";
            switch(docsrc){
                case "lamming":
                    res = "Interactive System Design (Newman and Lamming)";
                    break;
                case "shnm":
                    res = "Designing User Interface (Shneiderman)";
                    break;
                case "preece":
                    res = "Interaction Design (Preece, Rogers and Sharp)";
                    break;
                case "dix": 
                    res = "Human Computer Interaction (Dix)";
                    break;
                case "lewis": 
                    res = "Task-Centered User Interface Design (Lewis and Rieman)";
                    break;
                case "tdo": 
                    res = "The Discipline of Organizing"; // @@@@
                    break;
				case "iir": 
                    res = "Introdcution to Information Retrieval"; // @@@@
                    break;
				case "mir": 
                    res = "Modern Information Retrieval"; // @@@@
                    break;
            }
            return res;
        }
        
        function selectFunction(lecture, opacity) {
            d3.selectAll("." + lecture).style("opacity", opacity);
            return false;
        }
        
        $(document).ready(function () {
            
            //alert('Reader load!');
			
			var extend = function() {
				// make sure tab extends to bottom of viewport
	            var tc = document.getElementById('tab-content');
	            var viewportHeight = $(window).height();
	            var tcTop = tc.getBoundingClientRect().top;
	            var height = Math.max(0, viewportHeight - tcTop);
	            tc.style.height = height + 'px';
			};

			extend();

			$(window).on('resize orientationChange', function(event) {
				extend();
			});
        
        });
    </script>

<section id="tabs">
  <section class="tabbable">
    <ul class="nav nav-tabs" id="navTab">
      <li class="active"><a href="#tab1" data-toggle="tab">Index</a></li>
      <li><a href="#tab3" data-toggle="tab">Peer Comparison</a></li>
      <li><a href="#tab4" data-toggle="tab">My Progress</a></li>
    </ul>
    <section class="tab-content" id="tab-content">
      <section class="tab-pane fade active in" id="tab1">
        <section id="index">
		<a href="https://docs.google.com/presentation/d/1co5QR58Z4TwyD6MY68BC_LAEsQ49Zo4JAJ-XwlZxCOE/edit?usp=sharing" target= "_blank" style="position:absolute;top:5px;right:10px;font-weight:bold;color:#FE9A2E">Help?</a>
        <button type="button" id="toggle-question-status" style = "height:25px;display:none">Q/A Status</button>
		<button type="button" id="hidden-question-status" style = "display:none"></button>
        </section>
      </section>
      <section class="tab-pane fade " id="tab3">

        <div class="flexy-column">
<script>
var timep=0;
$("#lec1-chapter").hide();
$(document).ready(function(){
                  $("#peter-btn").click(function(){
                     var div=$("#peter");
                     if(timep === 1){
                       div.animate({height:'200px',opacity:'1.0'},"slow");
                       div.show();
                       //$("#lec1").hide()
                       //$("#lec1").animate({height:'150px',opacity:'0.0'},"slow");
                       timep = timep - 1;
                     }
                       //div.animate({width:'300px',opacity:'0.8'},"slow");
                     else if(timep === 0){
                       div.animate({height:'0px',opacity:'0.0'},"slow");
                       //$("#lec1").animate({height:'160px',opacity:'1.0'},"slow");
                       //$("#lec1").show()
                       div.hide();
                       //document.write(time);
                       timep = timep +1;
                     }
                       //div.animate({width:'100px',opacity:'0.8'},"slow");
                });
            });
</script>
            <button id="peter-btn">Peter Brusilovsky</button>
            <div id="peter" class="progress-factor flexy-item">
                <div class="progress-bar">
                    <div class="bar has-rotation has-colors orange ruler-2" role="progressbar" aria-valuenow="64" aria-valuemin="0" aria-valuemax="100">
                        <div class="tooltip white"></div>
                        <div class="bar-face face-position roof percentage"></div>
                        <div class="bar-face face-position back percentage"></div>
                        <div class="bar-face face-position floor percentage volume-lights"></div>
                        <div class="bar-face face-position left"></div>
                        <div class="bar-face face-position right"></div>
                        <div class="bar-face face-position front percentage volume-lights shine">7/14</div>
                    </div>
                </div>
            </div>
<script>
var timel=0;
$("#lec1-chapter").hide();
$(document).ready(function(){
                  $("#lin-btn").click(function(){
                                        var div=$("#lin");
                                        if(timel === 1){
                                        div.animate({height:'200px',opacity:'1.0'},"slow");
                                        //div.show();
                                        //$("#lec1").hide()
                                        //$("#lec1").animate({height:'150px',opacity:'0.0'},"slow");
                                        timel = timel - 1;
                                        }
                                        //div.animate({width:'300px',opacity:'0.8'},"slow");
                                        else if(timel === 0){
                                        div.animate({height:'0px',opacity:'0.0'},"slow");
                                        //$("#lec1").animate({height:'160px',opacity:'1.0'},"slow");
                                        //$("#lec1").show()
                                       // div.hide();
                                        //document.write(time);
                                        timel = timel +1;
                                        }
                                        //div.animate({width:'100px',opacity:'0.8'},"slow");
                                        });
                  });
</script>
<button id="lin-btn">yi-ling lin</button>
<div id="lin" class="progress-factor flexy-item">
<div class="progress-bar">
<div class="bar has-rotation has-colors orange ruler-2" role="progressbar" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100">
<div class="tooltip white"></div>
<div class="bar-face face-position roof percentage"></div>
<div class="bar-face face-position back percentage"></div>
<div class="bar-face face-position floor percentage volume-lights"></div>
<div class="bar-face face-position left"></div>
<div class="bar-face face-position right"></div>
<div class="bar-face face-position front percentage volume-lights shine">3/14</div>
</div>
</div>
</div>
        </div>
          <!-- <div id="chart2Btn"><a href="#">0</a> <a href="#">1</a> <a href="#">2</a></div>  -->

        <!--div id="guidelines"> <strong>Navigate lecture readings by browsing the sections in top circle.</strong>
          <ul>
            <li>By <b>clicking on a subsection</b>, you will open a dialog that show links to your lectures.</li>
            <li> By <b>mouse over</b>, you will compare your progress to your peers' progress (P1, P2 and P3) in the circles
              beneath this text
            <li> Use your <b>mouse wheel over the circle</b>, to zoom in and out. You can also <b>drag</b> the circle.
          </ul>
        </div-->
      </section>
      <section class="tab-pane fade" id="tab4">
        <section id="self-comp">
            <div class="flexy-column">
            <script>
                var time=0;
                $("#lec1-chapter").hide();
                $(document).ready(function(){
                    $("#lec1-btn").click(function(){
                        var div=$("#lec1-chapter");
                        if(time === 1){
                            div.animate({height:'600px',opacity:'1.0'},"slow");
                            div.show();
                            //$("#lec1").hide()
                            $("#lec1").animate({height:'150px',opacity:'0.0'},"slow");
                            time = time - 1;
                        }
                                         //div.animate({width:'300px',opacity:'0.8'},"slow");
                        else if(time === 0){
                            div.animate({height:'0px',opacity:'0.0'},"slow");
                            $("#lec1").animate({height:'160px',opacity:'1.0'},"slow");
                            $("#lec1").show()
                            div.hide();
                                         //document.write(time);
                            time = time +1;
                        }
                                         //div.animate({width:'100px',opacity:'0.8'},"slow");
                    });
                });
            </script>
            <button id="lec1-btn">Lecture 1</button>
            <div  style="height=0px" id="lec1-chapter">
                <a onclick="javascript:parent.parent.frames['iframe-content'].location = './reader.php?bookid=lamming&docno=lamming-0002&usr=zhw59&grp=IS2470Spring2016&sid=05F89&page=1';">chapter 1 </a>
                <div class="progress-factor flexy-item">
                    <div class="progress-bar">

                        <div class="bar has-rotation has-colors red heat-gradient" role="progressbar" aria-valuenow="99" aria-valuemin="0" aria-valuemax="100">
                            <div class="tooltip heat-gradient-tooltip"></div>
                            <div class="bar-face face-position roof percentage"></div>
                            <div class="bar-face face-position back percentage"></div>
                            <div class="bar-face face-position floor percentage volume-lights"></div>
                            <div class="bar-face face-position left"></div>
                            <div class="bar-face face-position right"></div>
                            <div class="bar-face face-position front percentage volume-lights shine">100%</div>
                        </div>
                    </div>
                </div>
                <a onclick="javascript:parent.parent.frames['iframe-content'].location = './reader.php?bookid=lamming&docno=lamming-0002&usr=zhw59&grp=IS2470Spring2016&sid=05F89&page=2';">chapter 2</a>
                <div class="progress-factor flexy-item">
                        <div class="progress-bar">
                            <div class="bar has-rotation has-colors red heat-gradient" role="progressbar" aria-valuenow="99" aria-valuemin="0" aria-valuemax="100">
                                <div class="tooltip heat-gradient-tooltip"></div>
                                <div class="bar-face face-position roof percentage"></div>
                                <div class="bar-face face-position back percentage"></div>
                                <div class="bar-face face-position floor percentage volume-lights"></div>
                                <div class="bar-face face-position left"></div>
                                <div class="bar-face face-position right"></div>
                                <div class="bar-face face-position front percentage volume-lights shine">100%</div>
                            </div>
                        </div>
                </div>
                <a onclick="javascript:parent.parent.frames['iframe-content'].location = './reader.php?bookid=lamming&docno=lamming-0002&usr=zhw59&grp=IS2470Spring2016&sid=05F89&page=3';">chapter 3</a>
                <div class="progress-factor flexy-item">
                    <div class="progress-bar">
                        <div class="bar has-rotation has-colors red heat-gradient" role="progressbar" aria-valuenow="99" aria-valuemin="0" aria-valuemax="100">
                            <div class="tooltip heat-gradient-tooltip"></div>
                            <div class="bar-face face-position roof percentage"></div>
                            <div class="bar-face face-position back percentage"></div>
                            <div class="bar-face face-position floor percentage volume-lights"></div>
                            <div class="bar-face face-position left"></div>
                            <div class="bar-face face-position right"></div>
                            <div class="bar-face face-position front percentage volume-lights shine">100%</div>
                        </div>
                    </div>
                </div>
                <a onclick="javascript:parent.parent.frames['iframe-content'].location = './reader.php?bookid=lamming&docno=lamming-0002&usr=zhw59&grp=IS2470Spring2016&sid=05F89&page=4';">chapter 4</a>
                <div class="progress-factor flexy-item">
                    <div class="progress-bar">
                        <div class="bar has-rotation has-colors red heat-gradient" role="progressbar" aria-valuenow="99" aria-valuemin="0" aria-valuemax="100">
                            <div class="tooltip heat-gradient-tooltip"></div>
                            <div class="bar-face face-position roof percentage"></div>
                            <div class="bar-face face-position back percentage"></div>
                            <div class="bar-face face-position floor percentage volume-lights"></div>
                            <div class="bar-face face-position left"></div>
                            <div class="bar-face face-position right"></div>
                            <div class="bar-face face-position front percentage volume-lights shine">100%</div>
                        </div>
                    </div>
                </div>
                </div>
                <div id="lec1" class="progress-factor flexy-item">
                    <div class="progress-bar">
                        <div class="bar has-rotation has-colors red heat-gradient" role="progressbar" aria-valuenow="99" aria-valuemin="0" aria-valuemax="100">
                                <div class="tooltip heat-gradient-tooltip"></div>
                                <div class="bar-face face-position roof percentage"></div>
                                <div class="bar-face face-position back percentage"></div>
                                <div class="bar-face face-position floor percentage volume-lights"></div>
                                <div class="bar-face face-position left"></div>
                                <div class="bar-face face-position right"></div>
                                <div class="bar-face face-position front percentage volume-lights shine">100%</div>
                        </div>
                    </div>
                </div>
                <script>
                    var time2=0;
                    $(document).ready(function(){
                        $("#lec2-btn").click(function(){
                            var div=$("#lec2-chapter");
                            if(time2 === 1){
                                div.animate({height:'600px',opacity:'1.0'},"slow");
                                $("#lec2").animate({height:'0px',opacity:'0.0'},"slow");
                                time2 = time2 - 1;
                            }
                                       //div.animate({width:'300px',opacity:'0.8'},"slow");
                            else if(time2 === 0){
                                div.animate({height:'0px',opacity:'0.0'},"slow");
                                $("#lec2").animate({height:'160px',opacity:'1.0'},"slow");
                                    //document.write(time);
                                time2 = time2 +1;
                            }
                                       //div.animate({width:'100px',opacity:'0.8'},"slow");
                        });
                    });
                </script>
                    <button id="lec2-btn">Lecture 2</button>
                    <div  style="height=0px" id="lec2-chapter">
                        <a onclick="javascript:parent.parent.frames['iframe-content'].location = './reader.php?bookid=lamming&docno=lamming-0002&usr=zhw59&grp=IS2470Spring2016&sid=05F89&page=1';">chapter 1 </a>
                        <div class="progress-factor flexy-item">
                            <div class="progress-bar">
                                <div class="bar has-rotation has-colors red heat-gradient" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                    <div class="tooltip heat-gradient-tooltip"></div>
                                    <div class="bar-face face-position roof percentage"></div>
                                    <div class="bar-face face-position back percentage"></div>
                                    <div class="bar-face face-position floor percentage volume-lights"></div>
                                    <div class="bar-face face-position left"></div>
                                    <div class="bar-face face-position right"></div>
                                    <div class="bar-face face-position front percentage volume-lights shine">50%</div>
                                </div>
                            </div>
                        </div>
                        <a onclick="javascript:parent.parent.frames['iframe-content'].location = './reader.php?bookid=lamming&docno=lamming-0002&usr=zhw59&grp=IS2470Spring2016&sid=05F89&page=1';">chapter 2</a>
                        <div class="progress-factor flexy-item">
                            <div class="progress-bar">
                                <div class="bar has-rotation has-colors red heat-gradient" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
                                    <div class="tooltip heat-gradient-tooltip"></div>
                                    <div class="bar-face face-position roof percentage"></div>
                                    <div class="bar-face face-position back percentage"></div>
                                    <div class="bar-face face-position floor percentage volume-lights"></div>
                                    <div class="bar-face face-position left"></div>
                                    <div class="bar-face face-position right"></div>
                                    <div class="bar-face face-position front percentage volume-lights shine">40%</div>
                                </div>
                            </div>
                        </div>
                        <a onclick="javascript:parent.parent.frames['iframe-content'].location = './reader.php?bookid=lamming&docno=lamming-0002&usr=zhw59&grp=IS2470Spring2016&sid=05F89&page=1';">chapter 3</a>
                            <div class="progress-factor flexy-item">
                                <div class="progress-bar">
                                    <div class="bar has-rotation has-colors red heat-gradient" role="progressbar" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100">
                                        <div class="tooltip heat-gradient-tooltip"></div>
                                        <div class="bar-face face-position roof percentage"></div>
                                        <div class="bar-face face-position back percentage"></div>
                                        <div class="bar-face face-position floor percentage volume-lights"></div>
                                        <div class="bar-face face-position left"></div>
                                        <div class="bar-face face-position right"></div>
                                        <div class="bar-face face-position front percentage volume-lights shine">30%</div>
                                    </div>
                                </div>
                            </div>
                        <a onclick="javascript:parent.parent.frames['iframe-content'].location = './reader.php?bookid=lamming&docno=lamming-0002&usr=zhw59&grp=IS2470Spring2016&sid=05F89&page=1';">chapter 4</a>
                            <div class="progress-factor flexy-item">
                                <div class="progress-bar">
                                    <div class="bar has-rotation has-colors red heat-gradient" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100">
                                        <div class="tooltip heat-gradient-tooltip"></div>
                                        <div class="bar-face face-position roof percentage"></div>
                                        <div class="bar-face face-position back percentage"></div>
                                        <div class="bar-face face-position floor percentage volume-lights"></div>
                                        <div class="bar-face face-position left"></div>
                                        <div class="bar-face face-position right"></div>
                                        <div class="bar-face face-position front percentage volume-lights shine">45%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="lec2" class="progress-factor flexy-item">
                            <div class="progress-bar">
                                <div class="bar has-rotation has-colors red heat-gradient" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                    <div class="tooltip heat-gradient-tooltip"></div>
                                    <div class="bar-face face-position roof percentage"></div>
                                    <div class="bar-face face-position back percentage"></div>
                                    <div class="bar-face face-position floor percentage volume-lights"></div>
                                    <div class="bar-face face-position left"></div>
                                    <div class="bar-face face-position right"></div>
                                    <div class="bar-face face-position front percentage volume-lights shine">50%</div>
                                </div>
                            </div>
                        </div>


            </div>
        </section>
      </section>
    </section>
  </section>
</section>
<script id="sunid" type="text/javascript" src="small-multiples.js"></script>
<script type='text/javascript' src='js/jquery.simplemodal.js'></script>
<script type='text/javascript' src='js/basic.js'></script>
<script type='text/javascript' src='indexTree.js'></script>
<script type='text/javascript' src='selfcompare.js'></script>
<script type="text/javascript" src="chosen/chosen.jquery.js"></script>
