var mobileRecertsChart = document.getElementById("recerts");
  var mobileRecerts = new Chart(mobileRecertsChart, {
      type: 'doughnut',
      data: {
          labels: ["-30 Days", "< 30 Days", "31-60 Days", "61-90 Days"],
          datasets: [{
              data: [2, 18, 17, 65],
              backgroundColor: [
                  '#cb4154',
                  '#ffc845',
                  '#84bd00',
                  '#00a3e0'
              ],
              color: [ 
                'white'
              ],
              borderWidth: 0,
              lineTension: 0,
              pointStyle: 'circle'
          }],
      },
      options: {
          responsive: true,
          legend: {
            position: 'left',
            color: 'white'
          }
      }    
  });

  var new_members = {};
  /** get some random users  */
  fetch('https://randomuser.me/api?&inc=gender,name,picture,email,registered&results=9&nat=US')
    .then(function(response) {
      return response.json();
    })
    .then(function(data) {
      //console.log(myJson);
        for (var i = 0; i < 9; i++) {
          var name = data.results[i]['name']['first'] + " " + data.results[i]['name']['last'];
          
            if (i < 4) {
              document.querySelectorAll('.block img')[i].src = data.results[i]['picture']['medium'];
              document.querySelectorAll('.block a')[i].innerHTML = data.results[i]['email'];
              document.querySelectorAll('.block h2')[i].innerHTML = name;
              document.querySelectorAll('.block span')[i].innerHTML = moment(data.results[i]['registered']).format('MM/DD/YYYY');              
            } else if (i < 8) {
              document.querySelectorAll('.block img')[i].src = data.results[i]['picture']['medium'];
              document.querySelectorAll('.block span')[i].innerHTML = name;
            }  else {
            document.querySelectorAll('.account__avatar img')[0].src = data.results[i]['picture']['medium'];  
            document.querySelectorAll('.account__avatar span')[0].innerHTML = name; 
          }
          //document.querySelectorAll('.block p')[i].prepend(name + " ");
        }        
    });

  document.querySelectorAll('form.member_search')[0].addEventListener('submit', function(evt){
      evt.preventDefault();

      let _errors = "";
      if (document.querySelector('[name="user"]').value < 1) {
        _errors += "Please enter a username to send a message to. \n\n";
      }
      if (document.querySelector('[name="message"]').value < 1) {
        _errors += "Please enter a message to send to the user. \n\n";
      }
      // let user know if there are errors
      if (_errors != '') {        
        alert(_errors);
      } else {
        document.querySelector('[name="user"]').style.display = 'none';
        document.querySelector('[name="message"]').style.display = 'none';
        document.querySelectorAll('form.member_search button')[0].style.display = 'none';  
        document.querySelectorAll('.success')[0].className = 'success sent';          
      }
  }); 

  document.querySelectorAll('.alert')[0].addEventListener('click', function(evt){
    evt.preventDefault();
    this.style.display = 'none';
  });                   