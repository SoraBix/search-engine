$(function()
{
  var suggestions = [];

  $("#q").autocomplete(
  {
    source : function(request, response)
    {
      var current_term = "";
      var previous_term = "";

      var query = $("#q").val().toLowerCase();
      var space_index = query.lastIndexOf(' ');

      if(query.length-1 > space_index && space_index != -1)
      {
        current_term = query.substr(space_index+1);
        previous_term = query.substr(0, space_index);
      }
      else
      {
        current_term = query; 
      }

      var URL = "http://localhost:8983/solr/homework/suggest?q=" + current_term + "&wt=json&indent=true";

      $.ajax(
      {
        url : URL, success : function(data)
        {
          var suggestionJSON = data.suggest.suggest[current_term].suggestions;
          var num = Math.min(suggestionJSON.length, 5);

          for(var i = 0 ; i < num ; i++)
          {
            suggestions[i] = $.trim(previous_term + " " + suggestionJSON[i].term);
          }

          console.log(suggestions);
          response(suggestions);
        }, dataType : 'jsonp', jsonp : 'json.wrf'});
    }
  })
});