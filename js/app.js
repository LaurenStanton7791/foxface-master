var trafficWeeklyChart = document.getElementById("traffic_weekly");
  var trafficWeekly = new Chart(trafficWeeklyChart, {
      options: {
          legend: {
              display: false
          }
      },
      type: 'line',
      data: {
          labels: ["16-22", "23-29", "30-5", "6-12", "13-19", "20-26", "27-3", "4-10", "11-7", "18-24", "25-31"],
          datasets: [{
              data: [750, 1250, 1350, 1550, 1300, 1550, 1950, 1700, 2100, 1750, 2250],
              borderColor: [
                  '#7379bd'
              ],
              backgroundColor: '#e2e3f5',
              borderWidth: 1,
              lineTension: 0,
              pointStyle: 'circle'
          }],
      }
  });

  var trafficDailyChart = document.getElementById("traffic_daily");
  var trafficDaily = new Chart(trafficDailyChart, {
      options: {
          legend: {
              display: false
          }
      },
      type: 'bar',
      data: {
          labels: ["S", "M", "T", "W", "Th", "F", "S"],
          datasets: [{
              data: [75, 150, 130, 150, 130, 200, 220],
              borderColor: [
                  '#4d4d71'
              ],
              backgroundColor: '#4d4d71',
              borderWidth: 0,
              borderRadius: 5,
              lineTension: 0,
              pointStyle: 'circle'
          }],
      }
  });

  var mobileUsersChart = document.getElementById("mobile_users");
  var mobileUsers = new Chart(mobileUsersChart, {
      type: 'doughnut',
      data: {
          labels: ["Phones", "Tablets", "Desktops"],
          datasets: [{
              data: [18, 17, 65],
              backgroundColor: [
                  '#83c891',
                  '#76b1be',
                  '#7379bd'
              ],
              borderWidth: 1,
              lineTension: 0,
              pointStyle: 'circle'
          }],
      },
      options: {
          responsive: true,
          legend: {
            position: 'right'
          }
      }    
  });

  function formatDate(date) {
      var monthNames = [
          "January", "February", "March",
          "April", "May", "June", "July",
          "August", "September", "October",
          "November", "December"
      ];

      var day = date.getDate();
      var monthIndex = date.getMonth();
      var year = date.getFullYear();

      return day + ' ' + monthNames[monthIndex] + ' ' + year;
  }

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