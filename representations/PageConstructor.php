<?php
ini_set('memory_limit', '-1');

function __construct()
{
    generate_page();
}

function generate_page()
{
    $head = generate_head();
    $footer = generate_footer();

    pre_graph_q4_generation();

    $pageQ1 = $head
        . hr()
        . generate_histogram()
        . $footer;
    write("q1.html", $pageQ1);

    $pageQ2 = $head
        . hr()
        . generate_pie_chart()
        . $footer;
    write("q2.html", $pageQ2);

    $pageQ3 = $head
        . hr()
        . generate_graph_q3()
        .generate_caption()
        . $footer;
    write("q3.html", $pageQ3);

    $pageQ4 = $head
        . hr()
        . generate_graph_q4()
        ."<p style='margin-left: 2%'>Représentation limitée à 1000 points.</p>"
        ."<p style='margin-left: 2%'>Le noir symbolise l'incidence des crimes sur un point similaire.</p>"
        .generate_caption("Non elucidé","Elucidé")
        . $footer;
    write("q4.html", $pageQ4);

    $pageQ5 = $head
        . hr()
        . generate_graph_q5()
        . $footer;
    write("q5.html", $pageQ5);
}

function hr()
{
    return "<hr>";
}

function write($fileName, $content)
{
    $myfile = fopen($fileName, "w") or die("Unable to open file!");
    fwrite($myfile, $content);
    fclose($myfile);
}

function generate_head()
{
    return "
            <!DOCTYPE html>
            <html lang=\"en\">
            <head>
                <meta charset=\"UTF-8\">
                <title>BERTRAND Guillaume - Questions BigData</title>
                <link rel=stylesheet href=\"../node_modules/bootstrap/dist/css/bootstrap.min.css\">
                <link rel=stylesheet href=\"style.css\">
                <script type=\"application/javascript\" src=\"../node_modules/chart.js/dist/Chart.min.js\"></script>
            <link
            rel=\"stylesheet\"
            href=\"http://cdn.leafletjs.com/leaflet-0.7/leaflet.css\"/>
            <script src=\"http://d3js.org/d3.v3.min.js\"></script>

            <script src=\"http://cdn.leafletjs.com/leaflet-0.7/leaflet.js\"></script>
            </head>
            <body>
            <ul>
                <li><a href=\"./q1.html\">Q1</a></li>
                <li><a href=\"./q2.html\">Q2</a></li>
                <li><a href=\"./q3.html\">Q3</a></li>
                <li><a href=\"./q4.html\">Q4</a></li>
                 <li><a href=\"./q5.html\">Q5</a></li>
            </ul>
            <div class=\"container\">
            ";
}

function generate_footer()
{
    return "<p id=\"copyleft\"><i>Projet M2SID 2018 - BERTRAND Guillaume</i></p></div>
</div>
</body>
</html>";
}

function encapsulate_in_row($content)
{
    $before = "<div class=\"row\">";
    $after = "</div>";
    return $before . $content . $after;
}

function generate_graphs()
{
    $graphs = encapsulate_in_row(generate_histogram($question = "Question 1 - Donnez le classement décroissant des catégories de crimes", "q1", "chartq1"));
    $graphs .= encapsulate_in_row(generate_graph_q4());
    return $graphs;
}

function extract_data_q($directory = "q1")
{


    $labels = "";
    $data = "";
    $files = glob("../$directory/part-*");
    $count = 0;

    foreach ($files as $file) {
        $myfile = fopen($file, "r") or die("Unable to open file!");
        if ($myfile) {
            while (($line = fgets($myfile)) !== false) {
                $cleanLine = substr($line, 1, -2);
                $splittedLine = explode(",", $cleanLine);
                $labels .= '"' . $splittedLine[0] . '"' . ',';
                $data .= $splittedLine[1] . ',';
                $count += 1;
            }

            fclose($myfile);
        } else {
            // error opening the file.
        }
    }
    $labels = substr($labels, 0, -1);
    $data = substr($data, 0, -1);
    return [$labels, $data, $count];
}

function extract_data_q4($directories = ["q4"], $fileName = "circles.json")
{
    $jsonHead = "{\"objects\":[";
    $jsonFoot = "]}";
    $json = "";
    $lineLeft = "{\"circle\":{\"coordinates\":[";
    $lineRight = "]}},";

    foreach ($directories as $directory) {
        $json .= extractDirectory($directory, $lineLeft, $lineRight, $json);
    }
    $json = substr($json, 0, -1);
    write($fileName, $jsonHead . $json . $jsonFoot);
}

function extractDirectory($directory, $lineLeft, $lineRight, $json)
{
    $files = glob("../$directory/part-*");

    $i = 0;
    foreach ($files as $file) {
            $myfile = fopen($file, "r") or die("Unable to open file!");
            if ($myfile) {
                while ((($line = fgets($myfile)) !== false) && $i<1000) {
                    $extractedValue = substr($line, 1, -2);
                    if ($directory == "q4") {
                        $extract = explode(",", $extractedValue);
                        $extract[0] = substr($extract[0], 1);
                        $extract[2] = substr($extract[2], 0, -1);

                        $json .= $lineLeft . $extract[1] . "," . $extract[2] . "," . $extract[0] . "," . $extract[3] . $lineRight;
                    } else {
                        if ($directory == "q3-plus-dangereux") {
                            $json .= $lineLeft . $extractedValue . ", false" . $lineRight;
                        } else {
                            $json .= $lineLeft . $extractedValue . ", true" . $lineRight;
                        }
                    }
                    $i++;
                }

                fclose($myfile);
            } else {
                // error opening the file.
            }
    }
    return $json;
}

function getRandomColor($length)
{
    $backGroundsColors = '[';
    $borderColors = '[';

    for ($i = 0; $i < $length; $i++) {
        $color = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        $color2 = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        $backGroundsColors .= '"' . $color . '",';
        $borderColors .= '"' . $color2 . '",';
    }
    $backGroundsColors .= ']';
    $borderColors .= ']';

    return [$backGroundsColors, $borderColors];
}

;

function generate_histogram($question = "Question 1 - Donnez le classement décroissant des catégories de crimes", $q = "q1", $id = "chartq1")
{
    $extractFields = extract_data_q($q);
    $colors = getRandomColor($extractFields[2]);
    $backGroundsColors = $colors[0];
    $borderColors = $colors[1];

    $graph1 = "
        <div class=\"col-12\">
        <h2>$question</h2>
        <canvas id=\"$id\"></canvas>
        </div>
            ";

    $datasets = "";
    $i = 0;
    $arr = explode(",", $extractFields[1]);
    $labels = explode(",", $extractFields[0]);
    foreach ($arr as $data) {
        $color = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
        $labels[$i] = substr($labels[$i], 1, -1);
        $datasets .= "{
                label: ['$labels[$i]'],
                data: [$data],
                backgroundColor: ['$color'],
                //borderColor: 
                //borderWidth: 1
            },";
        $i++;
    }
    $datasets = substr($datasets, 0, -1);

    $js_graph1 = "
    <script type=\"application/javascript\">
    
    var ctx = document.getElementById(\"$id\").getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [$datasets]
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero:true
                    }
                }]
            }
        }
    });
</script>
";
    return $graph1 . $js_graph1;
}

function generate_pie_chart($question = "Question 2 - Donnez le nombre de crimes en fonction de 6 plages horaires", $q = "q2", $id = "chartq2", $subTitle = "Répatition par plages horaires")
{
    $extractFields = extract_data_q($q);
    $colors = getRandomColor($extractFields[2]);
    $backGroundsColors = $colors[0];
    $borderColors = $colors[1];

    $graph1 = "
        <div class=\"col-12\">
        <h2>$question</h2>
        <canvas id=\"$id\"></canvas>
        </div>
            ";
    $js_graph1 = "
    <script type=\"application/javascript\">
    
    var ctx = document.getElementById(\"$id\").getContext('2d');
    
    new Chart(document.getElementById(\"$id\"), {
    type: 'polarArea',
    data: {
      labels: [$extractFields[0]],
      datasets: [
        {
          label: \"$subTitle\",
          backgroundColor: $backGroundsColors,
          data: [$extractFields[1]]
        }
      ]
    },
    options: {
      title: {
        display: true,
        text: '$subTitle'
      }
    }
});

</script>
";
    return $graph1 . $js_graph1;
}

function generate_graph_q2()
{
    return generate_histogram($question = "Question 2 - Donnez le nombre de crimes en fonction de 6 plages horaires", "q2", "chartq2");
}

function pre_graph_q4_generation()
{
    extract_data_q4();
}

function generate_graph_q4($fileName = "circles.json", $mapTitle = "Question 4 - Donnez la répartition géographique des crimes commis/élucidé", $mapId = "map", $markerSize = 40)
{
    $graph4 = "<div class=\"col-12\">
        <h2>$mapTitle</h2><div id=\"$mapId\" style=\"width: 600px; height: 400px\"></div></div>";
    $js_graph4 = "<script type=\"text/javascript\">
    var map = L.map('$mapId').setView([41.780595495,-87.68367553 ], 13);
    mapLink =
        '<a href=\"http://openstreetmap.org\">OpenStreetMap</a>';
    L.tileLayer(
        'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; ' + mapLink + ' Contributors',
            maxZoom: 18,
        }).addTo(map);

    // Initialize the SVG layer
    map._initPathRoot()

    // We pick up the SVG from the map object
    var svg = d3.select(\"#$mapId\").select(\"svg\"),
        g = svg.append(\"g\");


    // add a scale at at your map.
    var scale = L.control.scale().addTo(map); 

    d3.json(\"$fileName\", function(collection) {
        // Add a LatLng object to each item in the dataset
        collection.objects.forEach(function(d) {
            //if(d.circle.coordinates[0]!=null && d.circle.coordinates[1]!=null && d.circle.coordinates[2]!=null){
                d.LatLng = new L.LatLng(d.circle.coordinates[0],
                d.circle.coordinates[1], d.circle.coordinates[2])
            //}
        })

        var feature = g.selectAll(\"circle\")
            .data(collection.objects)
            .enter().append(\"circle\")
            .style(\"stroke\", \"black\")
            .style(\"opacity\", .6)
            .style('fill', function(d, i) {
                if(d.circle.coordinates[2]==true){
                    return \"blue\"
                }else{
                    return \"red\"
                }
            })
            .attr(\"r\", $markerSize)
            .style(\"stroke-width\", function(d) {
              return d.circle.coordinates[3];
            });

        map.on(\"viewreset\", update);
        update();

        function update() {
            // Get the label.
            var metres = scale._getRoundNum(map.containerPointToLatLng([0, map.getSize().y / 2 ]).distanceTo( map.containerPointToLatLng([scale.options.maxWidth,map.getSize().y / 2 ])))
            label = metres < 1000 ? metres + ' m' : (metres / 1000) + ' km';
            var currentKmsScale = (metres / 1000);
            
            var circles = document.getElementsByTagName(\"circle\"); 
            Array.prototype.forEach.call(circles, function(circleNode) {
              circleNode.setAttribute(\"r\", $markerSize/currentKmsScale);
            });
            
            feature.attr(\"transform\",
                function(d) {
                    return \"translate(\"+
                        map.latLngToLayerPoint(d.LatLng).x +\",\"+
                        map.latLngToLayerPoint(d.LatLng).y +\")\";
                }
            )
        }
    })
</script>
";
    return $graph4 . $js_graph4;
}

function generate_graph_q3()
{
    extract_data_q4(["q3-moins-dangereux", "q3-plus-dangereux"], "zones.json");
    $mapTitle = "Question 3 - Donnez les 3 zones les plus dangereuses et les zones les moins dangereuses";
    return generate_graph_q4("zones.json", $mapTitle, "mapq3", 75);
}

function generate_graph_q5()
{
    return generate_histogram($question = "Question 5 - Donnez le top 3 des mois les plus concernés par les cas de crime", "q5", "chartq5");
}

function generate_caption($red="Zones les plus dangereuses", $blue="Zones les moins dangereuses"){
    return "<div id=\"caption\">
    <p><div class=\"rectmdg\"></div>$blue</p>
    <p><div class=\"rectdg\"></div>$red</p>
</div>";
}
generate_page();