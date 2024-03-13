let booksterCPSWDomReady = function(callback) {
	document.readyState === "interactive" || document.readyState === "complete" ? callback() : document.addEventListener("DOMContentLoaded", callback);
};

booksterCPSWDomReady(() => {
  const DATE_FORMAT_UK = /^(\d{1,2})-(\d{1,2})-(\d{4})$/;
  const dateAdapter = {
    parse(value = "", createDate) {
      const matches = value.match(DATE_FORMAT_UK)
  
      if (matches) {
        return createDate(matches[3], matches[2], matches[1])
      }
    },
    format(date) {
      return `${date.getDate()}-${date.getMonth() + 1}-${date.getFullYear()}`
    },
  };
  const localization = {
    placeholder: "DD-MM-YYYY",
    buttonLabel: "Choose date",
    selectedDateMessage: "Selected date is",
    prevMonthLabel: "Previous month",
    nextMonthLabel: "Next month",
    monthSelectLabel: "Month",
    yearSelectLabel: "Year",
    closeLabel: "Close window",
    calendarHeading: "Choose a date",
    dayNames: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
    monthNames: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
    monthNamesShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
    locale: "en-GB"
  };

  let form = document.querySelector('#bookster-cpsw-form'),
  formSubmit = form.querySelector('.js-bookster-cpsw-submit'),
  dataPickers = form.querySelectorAll('.js-bookster-cpsw-date'),
  party = form.querySelector('.js-bookster-cpsw-party'),
  subID = form.querySelector('.js-bookster-cpsw-subId');

  dataPickers.forEach((el) => {
    el.dateAdapter = dateAdapter;
    el.localization = localization;
  });

  const checkIn = form.querySelector('.js-bookster-cpsw-check-in');
  const checkOut = form.querySelector('.js-bookster-cpsw-check-out');

  checkIn.addEventListener('duetChange', () => {
    let checkInObj = new Date(checkIn.value),
    checkOutObj = new Date(checkOut.value);

    if(checkInObj >= checkOutObj) {
      let newCheckInDay = checkOutObj.getDate() - 2;
      checkInObj.setDate(newCheckInDay);
      let month = checkInObj.getMonth() + 1;
      if(month < 10) month = '0'+month;
      checkIn.value = `${checkInObj.getFullYear()}-${month}-${checkInObj.getDate()}`;
    }
  });

  checkOut.addEventListener('duetChange', () => {
    let checkInObj = new Date(checkIn.value),
    checkOutObj = new Date(checkOut.value);

    if(checkOutObj <= checkInObj) {
      let newCheckOutDay = checkInObj.getDate() + 2;
      checkOutObj.setDate(newCheckOutDay);
      let month = checkInObj.getMonth() + 1;
      if(month < 10) month = '0'+month;
      checkOut.value = `${checkOutObj.getFullYear()}-${month}-${checkOutObj.getDate()}`;
    }
  });

  party.addEventListener('change', () => {
    if(party.value == '--') {
      formSubmit.setAttribute('disabled', 'disabled');
    } else formSubmit.removeAttribute('disabled');
  });

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    window.location = 'https://booking.booksterhq.com/find-and-book/availability/'+checkIn.value+'-until-'+checkOut.value+'-for-'+party.value+'/sub/'+subID.value;
  });
});