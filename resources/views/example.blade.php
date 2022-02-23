<html>
    <head>
        <title>Tablebuilder example page</title>
        <script type="text/javascript" src="https://code.jquery.com/jquery-3.x-git.min.js"></script>
        <script type="text/javascript" src="https://momentjs.com/downloads/moment.min.js"></script>
        <script type="text/javascript" src="js/vendor/seblhaire/tablebuilder/tablebuilder.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous"/>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" />
        <link rel="stylesheet" type="text/css" property="stylesheet" href="css/vendor/seblhaire/tablebuilder/tablebuilder.css"/>
    </head>
    <body>
      <style>
      .container {
        width:90%;
        max-width: 1500px;
      }
      </style>
      <br/><br/>
      <div class="container">
        {!! $oTable->output() !!}
        <br/><br/><br/><br/>
        <h4>Table inited with static data</h4>
        {!! $oTable2->output() !!}
        <br/><br/><br/><br/>
        <h4>Table inited with static data, no pagination, no search</h4>
        {!! $oTable3->output() !!}
        <script>
            var multiselect = function(data){
                console.log(data);
            }
            var checkboxclick = function(event){
                console.log(event.data.elt.prop('checked'));
                console.log(event.data.content);
                console.log(event.data.index);

            }
            var edit = function(id, lastname, firstname){
              alert('edit #' + id + ' ' + lastname + ' ' + firstname);
            }
            var eltspagechanged = function(iNbPages){
              alert(iNbPages + ' selected')
            }
            var aftertableload = function(tableobject, data){
              console.log(tableobject);
              console.log(data);
            }
        </script>

      </div>
    </body>
</html>
