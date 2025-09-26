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

  form = document.querySelector('#bookster-cpsw-form');

  let formSubmit = form.querySelector('.js-bookster-cpsw-submit'),
    dataPickers = form.querySelectorAll('.js-bookster-cpsw-date'),
    party = form.querySelector('.js-bookster-cpsw-party'),
    subID = form.querySelector('.js-bookster-cpsw-subId');

  dataPickers.forEach((el) => {
    el.dateAdapter = dateAdapter;
    el.localization = localization;
  });

  const checkIn = form.querySelector('.js-bookster-cpsw-check-in');
  const checkOut = form.querySelector('.js-bookster-cpsw-check-out');
  let dateToCheck = null;

  const isCheckInDateDisabled = (date) => {
    if (typeof apiData === 'undefined' || typeof apiData.dates !== 'object' || apiData.dates === null) {
      return false;
    }
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const formattedDate = `${year}-${month}-${day}`;
    const allowedDates = Object.keys(apiData.dates);
    return !allowedDates.includes(formattedDate);
  };

  checkIn.isDateDisabled = isCheckInDateDisabled;

  function dateCheck(date) {
    if (dateToCheck == null) return true;

    if ('range' in dateToCheck) {
      const from = new Date(dateToCheck.range.from + ' 00:00:00');
      const to = new Date(dateToCheck.range.to + ' 00:00:00');
      // The logic here is reversed: return false (enable) if in range.
      return !(date.getTime() >= from.getTime() && date.getTime() <= to.getTime());
    } else if ('possible' in dateToCheck) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      const formattedDate = `${year}-${month}-${day}`;
      return !dateToCheck.possible.includes(formattedDate);
    }
    // Add a default return for safety
    return true;
  }

  checkIn.addEventListener('duetChange', (e) => {
    if (typeof apiData != 'undefined' && apiData.dates[e.detail.value]) {
       dateToCheck = apiData.dates[e.detail.value];
    } else {
       dateToCheck = null; // Reset if the check-in date is invalid
    }

    checkOut.setAttribute("min", e.detail.value);

    var checkInDate = new Date(e.detail.value);
    var checkOutDate = new Date(checkOut.value);

    if (checkOutDate < checkInDate) {
      checkOutDate.setDate(checkInDate.getDate() + 1);
      
      const checkOutMonth = String(checkOutDate.getMonth() + 1).padStart(2, '0');
      const checkOutDay = String(checkOutDate.getDate()).padStart(2, '0');
      const newCheckOutDate = `${checkOutDate.getFullYear()}-${checkOutMonth}-${checkOutDay}`;
      
      checkOut.setAttribute("value", newCheckOutDate);
    }
  });

  checkOut.isDateDisabled = dateCheck;

  party.addEventListener('change', () => {
    if (party.value == '--') {
      formSubmit.setAttribute('disabled', 'disabled');
    } else formSubmit.removeAttribute('disabled');
  });

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    window.location = 'https://booking.booksterhq.com/find-and-book/availability/' + checkIn.value + '-until-' + checkOut.value + '-for-' + party.value + '/sub/' + subID.value;
  });
});