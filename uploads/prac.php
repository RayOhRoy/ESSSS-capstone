<?php
$xml_data=simplexml_load_file("sample.xml") or die ("Error loading XML file");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

        <input type="text" placeholder="type here" id="text_this">
        <button id="submitButton">SUBMIT</button>
        <div id="button1" style="display:none;"></div>
        
        <script>
        const submitButton = document.getElementById("submitButton");
        const textInput = document.getElementById("text_this");
        const foodContainer = document.getElementById("button1");

        submitButton.addEventListener("click", function () {
            const inputText = textInput.value;
            foodContainer.innerHTML = ""; 

            if (inputText === "adobo" || inputText === "sinigang" || inputText === "chapsoy") {
                <?php
                foreach ($xml_data->food as $data) {
                 
                    echo "foodContainer.innerHTML += '<p>yummy " . ($data->name) . "</p>';";
                }
                ?>
            }
            else if (inputText === "all") {
                <?php
                foreach ($xml_data->food as $data) {
                    echo "foodContainer.innerHTML += ' " . ($data->name) . "</p>';";
                    echo "foodContainer.innerHTML += ' " . ($data->price) . "</p>';";
                    echo "foodContainer.innerHTML += ' " . ($data->location) . "</p>';";
                    echo "foodContainer.innerHTML += ' " . ($data->date) . "</p>';";
                    echo "foodContainer.innerHTML += ' " . ($data->rate) . "</p>';";
                }
                ?>
            }else{
                foodContainer.innerHTML="<p>Not available</p>";
            }
            foodContainer.style.display = "block";
        });
    </script>



</body>
</html>