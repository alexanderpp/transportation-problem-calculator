var clients = 4; 
var dealers = 3;
var cost;
var supply;
var demand;

$(document).ready(function(){
    $(".container-fluid").css("min-height", ($(window).height() - 150)+"px");
    
    generateTable();
    tableFunction();
    
    $('#clients_select').on('change', function() {
        clients = this.value;
        generateTable();
        tableFunction();
    });
    
    $('#dealers_select').on('change', function() {
        dealers = this.value;
        generateTable();
        tableFunction();
    });
});
function generateTable(){
    var table = $("#toc");
    var html = "";
    
    html += "<thead>";
    html += "<th>";
    for(var i = 1; i <= clients; i++){
        html += "<th>Клиент "+i+"</th>";
    }
    html += "<th>Налична стока</th>";
    html += "</th>";
    html += "</thead>";
    html += "<tbody>";
    for(var i = 1; i <= dealers; i++){
        html += "<tr>";
        html += "<th>Доставчик "+i+"</th>";
        for(var j = 1; j <= clients; j++){
            if(j < clients){
                html += "<td class='value'>0</td>";
            } else {
                html += "<td class='value' style='border-right:1px solid steelblue'>0</td>";
            }
            
        }
        html += "<td class='supply'>0</td>";
        html += "</tr>";
    }
   
    html += "<tr>";
    html += "<th>Необходима стока</th>";
    for(var i = 1; i <= clients; i++){
        if(i < clients){
            html += "<td class='demand' style='border-top:1px solid steelblue;'>0</td>";
        } else {
            html += "<td class='demand' style='border-top:1px solid steelblue; border-right:1px solid steelblue'>0</td>";
        }
    }
    html += "<td class='result-btn' style='cursor:pointer; text-align:center; border-top:1px solid steelblue; background:steelblue; color:white;'>Намери решение</td>";
    html += "</tr>";
    html += "</tbody>";
    table.html(html);
    heavyTable();
    tableFunction();
}

function example(){
    clients = 4;
    dealers = 3;
    
    $("#clients_select option").filter(function() {
        return $(this).text() == clients; 
    }).prop('selected', true);
    
    $("#dealers_select option").filter(function() {
        return $(this).text() == dealers; 
    }).prop('selected', true);
    
    generateTable();
    
    cost =  [[2,3,11,7], [1,0,6,1], [5,8,15,9]];
    supply = [6,1,10];
    demand = [7,5,3,2];
    
    var i = 0;
    var j = 0;
    
    $(".value").each(function(){
        $(this).text(cost[i][j]);
        j++;
        if(j == clients){
            i++;
            j = 0;
        }
        
    });
    i = 0;
    $(".supply").each(function(){
        $(this).text(supply[i++]);
    });
    i=0;
    $(".demand").each(function(){
        $(this).text(demand[i++]);
    });
}


function getValues(){

    var i = 1;
    var j = 1;
    var array = {};
    array[i] = {};
    $(".value").each(function(){
        array[i][j++] = parseInt($(this).text());
        if(j > clients && i < dealers){
            i++;
            j = 1;
            array[i] = {};
        }
        
    });
    cost = JSON.stringify(array);
    
    array = {};
    i = 1;
    $(".supply").each(function(){
        array[i++] = parseInt($(this).text());
    });
    supply = JSON.stringify(array);
    
    array = {};
    i = 1;
    $(".demand").each(function(){
        array[i++] = parseInt($(this).text());
    });
    demand = JSON.stringify(array);
    
    getResutl();
}

function getResutl(){

    $.ajax({
        method: "POST",
        url: "calculator.php",
        data: { 
                clients: clients,
                dealers: dealers, 
                cost: cost, 
                supply: supply,
                demand: demand
              }
    }).done(function( msg ) {
        var result = $.parseJSON(msg);
        
        var html = "";
        for(var k in result) {
          html += '<div class="page-header">';
          html += '  <h1><small>Стъпка '+k+': '+result[k]["title"]+'</small></h1>';
          html += '</div>';
          html += '<p>'
          for(var v in result[k]["content"]) {
            html += result[k]["content"][v];
          }
          html += '</p>';
        }
        $("#steps").html(html);
    }).fail(function(){
        var html = "";
        html += '<div class="page-header">';
        html += '  <h1><small>Грешка</small></h1>';
        html += '</div>';
        html += '<p>Проблемът е грешен или отмена прекалено много време за да бъде решен!</p>'
    });
}

function heavyTable(){ 
(function($) {
  $.fn.heavyTable = function(params) {

    params = $.extend( {
      startPosition: {
        x: 1,
        y: 1
      }
    }, params);

    this.each(function() {
      var 
        $hTable = $(this).find('tbody'),
        i = 0,
        x = params.startPosition.x,
        y = params.startPosition.y,
        max = {
          y: $hTable.find('tr').length,
          x: $hTable.parent().find('thead').find('th').length
        };
      
      function clearCell() {    
        content = $hTable.find('.selected input').val();
        if(content !== undefined){
            content = parseInt(content);
            if(isNaN(content) || content < 0) content = 0;
        }
        $hTable.find('.selected').html(content);
        $hTable.find('.selected').toggleClass('selected');
      }

      function selectCell () {
        
        if ( y > max.y ) y = max.y;
        if ( x > max.x ) x = max.x;
        if ( y < 1 ) y = 1;
        if ( x < 2 ) x = 2;
        currentCell = 
         $hTable
            .find('tr:nth-child('+(y)+')')
            .find('td:nth-child('+(x)+')');
        content = currentCell.html();
        currentCell
          .toggleClass('selected')
        return currentCell;
      }
      
      function edit (currentElement) {
        if(x != max.x || y != max.y){
            var input = $('<input>', {type: "text"})
              .val("");
              
            var width = currentCell.width();
            input.width(width);
            currentElement.html(input);
            currentElement.width(width);
            input.focus(); 
        }
      }

      $hTable.find('td').click( function () {
        clearCell();
        x = (($hTable.find('td').index(this)) % (max.x - 1) + 2);
        y = ($hTable.find('tr').index($(this).parent()) + 1);
        edit(selectCell());
      });
      $hTable.find('.result-btn').click( function(){
        clearCell();
        getValues();
      });

      $(document).keydown(function(e){
        if (e.keyCode == 13) {
          clearCell();
          edit(selectCell());
        } else if (e.keyCode >= 37 && e.keyCode <= 40  ) {

          clearCell();
          switch (e.keyCode) {
            case 37: x--;
            break;
            case 38: y--;
            break;
            case 39: x++;
            break;
            case 40: y++;
            break;
          }
          selectCell();
          return false;
        }
      }); 
    });
  };
})(jQuery);
}
// call our jQuery function
function tableFunction(){
  $('.heavyTable').heavyTable({
    xPosition: 2,
    yPosition: 2
  });
}
