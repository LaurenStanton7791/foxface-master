<?php
require_once './inc/bootstrap.php';

requireAuth();
$user = findUserByAccessToken();

$data_catagory = $_GET["data_catagory"];

$res_questions = getResidentQuestions($data_catagory);

$carousel_loop = 1;
if ($data_catagory == 'INFO-OTH') {
    // see how many 'other' members we should loop thru
    $house_mems = getHouseholdMembersNum($user["user_id"]);
    $carousel_loop = $house_mems["house_mems"];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>FoxFace Questionnaire</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<style>
.carousel{
    background: lightgrey;
    margin-top: 20px;
    border-radius: 25px 25px 25px 25px;
    overflow: hidden;    
}
.carousel-item{
    text-align: center;
    min-height: 300px; /* Prevent carousel from being distorted if for some reason image doesn't load */  
}
</style>
</head>
<body>

<main class="container" role="main">
    <section class="py-4 py-md-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form class="form-horizontal" id="questionForm" action="/inc/process_questionnaire.php" method="POST">
                    <?php
                        echo '<input type="hidden" name="savePoint" id="savePoint">' . "\n";
                        echo '<input type="hidden" name="data_catagory" id="data_catagory" value="' . $data_catagory . '">' . "\n";
                    ?>
                    <div class="container-lg my-3">
                        <div id="formCarousel" class="carousel slide" data-ride="carousel" data-interval="false">
                            <div class="carousel-inner">
                                <div class="carousel-item  py-3 px-4 active">
                                    <h1>A questionnaire <?php echo "[" . $carousel_loop . "]"; ?></h1>
                                </div>

                                <?php
                                $javascript = "\n";
                                $slide_num = 0;
                                $last_slide = (int) count($res_questions) * (int) $carousel_loop;
                                
                                // Loop however many times as necessary (should do one)
                                for ($c_loop = 1; $c_loop <= $carousel_loop; $c_loop++) {
                                    // if carousel_loop is 1, then don't add any number to the end of the name/id fields 
                                    if ($carousel_loop == 1) { $n_loop = ''; } else { $n_loop = $c_loop; }
                                    
                                    foreach ($res_questions as &$res_question) {
                                        $slide_num++;
                                        
                                        echo '<div class="carousel-item py-3 px-4 ">' . "\n";
                                        echo '    <span style="color:darkolivegreen;font-weight:bold"><p>';
                                        
                                        // if the carousel_loop is greater than 1, write out [1 of nn]
                                        echo $slide_num . ' ' . $res_question["field_question"]; 
                                        if ($carousel_loop > 1) {
                                            echo ' [' . lookup_num_adjective($c_loop) . ' of ' . lookup_num_name($carousel_loop) . ']';
                                        }
                                        echo '</p></span><br><br>' . "\n";
                                        echo '    <div class="form-group">' . "\n";
                                        
                                        if ($res_question['data_type'] == 'text') {
                                            // See if this is a multi-part question
                                            $multi_fields = explode('|', $res_question["data_field"]);
                                            $multi_labels = explode('|', $res_question["options"]);
                                            if (count($multi_fields) > 1) {
                                                $multi_position = 0;
                                                foreach ($multi_fields as &$multi_field) {
                                                    $multi_label = $multi_labels[$multi_position];
                                                    $multi_position++;
                                                    echo '        <div class="form-row">' . "\n";
                                                    echo '            <label class="col-lg-6 control-label text-right col-form-label-lg" for="'. $multi_field . $n_loop .'">  ' . $multi_label . ':</label>' . "\n";
                                                    echo '            <div class="col-lg-6">' . "\n";
                                                    echo '                <input type="text" name="' . $multi_field . $n_loop . '" id="' . $multi_field . $n_loop . '" value="">' . "\n";
                                                    echo '            </div>' . "\n";
                                                    echo '        </div>' . "\n";
                                                }
                                            } else {
                                                $this_id = $res_question['data_field'];
                                                $multi_field_text = preg_replace('/(?<!\ )[A-Z]/', ' $0', $res_question['data_field']);
                                                echo '        <div class="form-row">' . "\n";
                                                echo '            <label class="col-lg-6 control-label text-right col-form-label-lg" for="'. $this_id .'">  ' . $res_question['options'] . ':</label>' . "\n";
                                                echo '            <div class="col-lg-6">' . "\n";
                                                echo '                <input type="text" name="' . $res_question['data_field'] . $n_loop . '" id="' . $this_id . $n_loop . '" value="">' . "\n";
                                                echo '            </div>' . "\n";
                                                echo '        </div>' . "\n";
                                            }
                                        }
                                        elseif ($res_question['data_type'] == 'radio') {
                                            $multi_fields = explode('|', $res_question["options"]);
                                            foreach ($multi_fields as &$multi_field) {
                                                // Get the first word from the option for the id field
                                                $multi_parts = explode('~',trim($multi_field));
                                                if (count($multi_parts) == 1) {
                                                    $multi_parts[1] = strtolower($multi_parts[0]);
                                                }
                                                $this_id = $res_question['data_field'] . "_" . $multi_parts[1];
    
                                                echo '        <div class="form-row">' . "\n";
                                                echo '            <label class="col-lg-6 control-label text-right col-form-label-lg" for="'. $this_id . $n_loop .'">  ' . $multi_parts[0] . ':</label>' . "\n";
                                                echo '            <div class="col-lg-6">' . "\n";
                                                echo '                <input type="radio" name="' . $res_question['data_field'] . $n_loop . '" id="' . $this_id . $n_loop . '" value="' . $multi_parts[1] . '">' . "\n";
                                                echo '            </div>' . "\n";
                                                echo '        </div>' . "\n";                                            
                                            }
                                        }
                                        elseif ($res_question['data_type'] == 'checkbox') {
                                            $multi_fields = explode('|', $res_question["options"]);
                                            foreach ($multi_fields as &$multi_field) {
                                                // Get the first word from the option for the id field
                                                $first = explode(' ',trim($multi_field));
                                                $this_id = $res_question['data_field'] . "_" . $first[0];
                                                $multi_field_text = preg_replace('/(?<!\ )[A-Z]/', ' $0', $multi_field);
                                                
                                                echo '        <div class="form-row">' . "\n";
                                                echo '            <label class="col-lg-6 control-label text-right col-form-label-lg" for="'. $this_id . $n_loop .'">  ' . $multi_field_text . ':</label>' . "\n";
                                                echo '            <div class="col-lg-6">' . "\n";
                                                echo '                <input type="radio" name="' . $res_question['data_field'] . $n_loop . '" id="' . $this_id . $n_loop . '" value="' . $multi_parts[1] . '">' . "\n";
                                                echo '            </div>' . "\n";
                                                echo '        </div>' . "\n";                                            
                                            }
                                        }
                                        elseif ($res_question['data_type'] == 'date') {
                                            $this_id = $res_question['data_field'];
                                            
                                            echo '        <div class="form-row">' . "\n";
                                            echo '            <label class="col-lg-6 control-label text-right col-form-label-lg" for="'. $this_id . $n_loop .'">  ' . $res_question["data_field"] . ':</label>' . "\n";
                                            echo '            <div class="col-lg-6">' . "\n";
                                            echo '                <input type="date" name="' . $res_question['data_field'] . $n_loop . '" id="' . $this_id . $n_loop . '" value="">' . "\n";
                                            echo '            </div>' . "\n";
                                            echo '        </div>' . "\n";                                            
                                        }
                                        elseif ($res_question['data_type'] == 'textarea') {
                                            $this_id = $res_question['data_field'];
    
                                            echo '        <div class="form-row">' . "\n";
                                            echo '            <label class="col-lg-6 control-label text-right col-form-label-lg" for="'. $this_id . $n_loop .'">  ' . $res_question["data_field"] . ':</label>' . "\n";
                                            echo '            <div class="col-lg-6">' . "\n";
                                            echo '                <textarea name="' . $res_question['data_field'] . $n_loop . '" id="' . $this_id . $n_loop . '" rows="3" cols="60"></textarea>' . "\n";
                                            echo '            </div>' . "\n";
                                            echo '        </div>' . "\n";                                            
                                        }                    
                                        elseif ($res_question['data_type'] == 'number') {
                                            $multi_fields = explode('|', $res_question["options"]);
                                            $this_id = $res_question['data_field'];
                                            
                                            echo '        <div class="form-row">' . "\n";
                                            echo '            <label class="col-lg-6 control-label text-right col-form-label-lg" for="'. $this_id . $n_loop .'">  ' . $res_question["data_field"] . ':</label>' . "\n";
                                            echo '            <div class="col-lg-6">' . "\n";
                                            echo '                <input type="number" name="' . $res_question['data_field'] . $n_loop . '" id="' . $this_id . $n_loop . '" min="' . $multi_fields[0] . '" max="' . $multi_fields[1] . '" step="' . $multi_fields[2] . '" value="">' . "\n";
                                            echo '            </div>' . "\n";
                                            echo '        </div>' . "\n";                                            
                                        }
                                        elseif ($res_question['data_type'] == 'dollar') {
                                            $multi_fields = explode('|', $res_question["options"]);
                                            $this_id = $res_question['data_field'];
                                            
                                            echo '        <div class="form-row">' . "\n";
                                            echo '            <label class="col-lg-6 control-label text-right col-form-label-lg" for="'. $this_id . $n_loop .'">  ' . $res_question["data_field"] . ':</label>' . "\n";
                                            echo '            <div class="col-lg-6">' . "\n";
                                            echo '                <input type="number" name="' . $res_question['data_field'] . $n_loop . '" id="' . $this_id . $n_loop . '"  min=".01" max="99999.99" step="0.01" value="">' . "\n";
                                            echo '            </div>' . "\n";
                                            echo '        </div>' . "\n";                                            
                                        }
                                           
                                        echo '    </div>' . "\n";
                                        echo '</div>' . "\n";
                                    }
                                }
                                
                                ?>
                            </div>
                                
                            <!-- Carousel controls -->
                            <div class="card-footer bg-transparent p-4 d-flex justify-content-between align-items-center">
                                <span>
                                    <button type="submit" class="btn btn-secondary mr-4 collapse animate__animated" id="saveAppBtn">Save & Exit</button>
                                </span>
                                <span>
                                    <a href="#formCarousel" class="btn btn-outline-primary mr-4 collapse animate__animated" id="previousQuestion" role="button" data-slide="prev">
                                        <span class="position-relative" style="top: -0.1em;">
                                            <svg class="bi bi-chevron-left" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 010 .708L5.707 8l5.647 5.646a.5.5 0 01-.708.708l-6-6a.5.5 0 010-.708l6-6a.5.5 0 01.708 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </span>
                                        <span>Back</span>
                                    </a>
                                    <a href="#formCarousel" class="btn btn-primary" id="nextQuestion" role="button" data-slide="next">
                                        <span id="nextText">Next</span>
                                        <span class="position-relative" style="top: -0.1em;">
                                            <svg class="bi bi-chevron-right" width="1em" height="1em" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 01.708 0l6 6a.5.5 0 010 .708l-6 6a.5.5 0 01-.708-.708L10.293 8 4.646 2.354a.5.5 0 010-.708z" clip-rule="evenodd"/>
                                            </svg>
                                        </span>
                                    </a>
                                </span>
                                
                            </div> <!-- carousel-inner -->
                        </div> <!-- carousel slide -->
                    </div> <!-- container-lg my-3 -->
                </form>
            </div> <!-- col-lg-7 -->
        </div> <!-- row justify-content-center -->
    </section>
</main>

<script>
    $(function () {
        const application = document.querySelector('form');
        
        document.querySelector('#nextText').innerHTML = 'Get Started';
        const nextQuestionBtn = document.querySelector('#nextQuestion');

        $('#formCarousel').on('slid.bs.carousel', function(e) {
            var slideFrom = $(this).find('.active').index();
            console.log("Made it to: " + slideFrom);
            
            if (slideFrom === <?php echo $last_slide; ?> ) {
                console.log("Made it to last");
                
                document.querySelector('#saveAppBtn').innerHTML = 'Submit';
                nextQuestionBtn.removeAttribute('data-slide');
                
                $('#nextQuestion').hide();
            } else {
                document.querySelector('#previousQuestion').classList.add('show', 'animate__fadeIn');
                document.querySelector('#saveAppBtn').classList.add('show', 'animate__fadeIn');

                document.getElementById("savePoint").value = slideFrom;
                
                application.addEventListener('submit', function(event) {
                    questionForm.submit;
                }, false);
                
                document.querySelector('#nextText').innerHTML = 'Next';
            }
        });
    });
</script>

</body>
</html>