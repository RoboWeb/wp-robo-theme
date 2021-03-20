/*!
 * classie - class helper functions
 * from bonzo https://github.com/ded/bonzo
 *
 * classie.has( elem, 'my-class' ) -> true/false
 * classie.add( elem, 'my-new-class' )
 * classie.remove( elem, 'my-unwanted-class' )
 * classie.toggle( elem, 'my-class' )
 */

/*jshint browser: true, strict: true, undef: true */
/*global define: false */

(function (window) {
  "use strict";

  // class helper functions from bonzo https://github.com/ded/bonzo

  function classReg(className) {
    return new RegExp("(^|\\s+)" + className + "(\\s+|$)");
  }

  // classList support for class management
  // altho to be fair, the api sucks because it won't accept multiple classes at once
  var hasClass, addClass, removeClass;

  if ("classList" in document.documentElement) {
    hasClass = function (elem, c) {
      return elem.classList.contains(c);
    };
    addClass = function (elem, c) {
      elem.classList.add(c);
    };
    removeClass = function (elem, c) {
      elem.classList.remove(c);
    };
  } else {
    hasClass = function (elem, c) {
      return classReg(c).test(elem.className);
    };
    addClass = function (elem, c) {
      if (!hasClass(elem, c)) {
        elem.className = elem.className + " " + c;
      }
    };
    removeClass = function (elem, c) {
      elem.className = elem.className.replace(classReg(c), " ");
    };
  }

  function toggleClass(elem, c) {
    var fn = hasClass(elem, c) ? removeClass : addClass;
    fn(elem, c);
  }

  var classie = {
    // full names
    hasClass: hasClass,
    addClass: addClass,
    removeClass: removeClass,
    toggleClass: toggleClass,
    // short names
    has: hasClass,
    add: addClass,
    remove: removeClass,
    toggle: toggleClass,
  };

  // transport
  if (typeof define === "function" && define.amd) {
    // AMD
    define(classie);
  } else {
    // browser global
    window.classie = classie;
  }
})(window);

/** MAIN */

(function () {
  var $body = document.body,
    content = document.getElementById("content"),
    openbtn = document.getElementById("mobile_menu_toggle"),
    isOpen = false;

  function init() {
    initEvents();
    // animatePointsOnMap();
  }

  function initEvents() {
    openbtn.addEventListener("click", toggleMenu);

    // close the menu element if the target it´s not the menu element or one of its descendants..
    content.addEventListener("click", function (ev) {
      var target = ev.target;
      if (isOpen && target !== openbtn) {
        toggleMenu();
      }
    });
  }

  // function animatePointsOnMap() {
  //   const pulsedMap = document.getElementById("pulsed_map");

  //   if (pulsedMap) {
  //     const mapImageWrapper = pulsedMap.parentNode;

  //     let pulse_1 = document.createElement("div");
  //     classie.add(pulse_1, "pulsed-orb");
  //     pulse_1.style.top = "1rem";
  //     pulse_1.style.left = "1rem";
  //     pulse_1.style.width = "16px";
  //     pulse_1.style.height = "16px";

  //     let pulse_2 = document.createElement("div");
  //     classie.add(pulse_2, "pulsed-orb");
  //     pulse_2.style.top = "10rem";
  //     pulse_2.style.left = "3rem";
  //     pulse_2.style.width = "32px";
  //     pulse_2.style.height = "32px";

  //     mapImageWrapper.appendChild(pulse_1);
  //     mapImageWrapper.appendChild(pulse_2);
  //   }
  // }

  function toggleMenu() {
    if (isOpen) {
      classie.remove($body, "show-menu");
    } else {
      classie.add($body, "show-menu");
    }
    isOpen = !isOpen;
  }

  init();
})();
