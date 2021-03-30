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
  let $body = document.body,
    content = document.getElementById("content"),
    openbtn = document.getElementById("mobile_menu_toggle"),
    _header = document.getElementById("header"),
    isOpen = false;

  function init() {
    removeScrollMeOnMobile();
    initEvents();
  }

  function removeScrollMeOnMobile() {
    console.log({
      width: window.screen.width,
      pixelRatio: window.devicePixelRatio,
    });

    if (window.screen.width <= 1024) {
      let scrollmeElements = document.getElementsByClassName("animateme"),
        i;
      if (scrollmeElements.length) {
        for (i = 0; i < scrollmeElements.length; i++) {
          console.log(scrollmeElements[i]);
          classie.remove(scrollmeElements[i], "scrollme");
        }
      }
    }
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

    document.addEventListener("scroll", _headerScrollPosition, false);
  }

  function toggleMenu() {
    if (isOpen) {
      classie.remove($body, "show-menu");
    } else {
      classie.add($body, "show-menu");
    }
    isOpen = !isOpen;
  }

  function _headerScrollPosition(event) {
    let isHeaderSticky = classie.has(_header, "is-sticky");
    let target = event.target;
    let scrollTop = event.target.scrollingElement.scrollTop;

    if (scrollTop > 96 && !isHeaderSticky) classie.add(_header, "is-sticky");
    if (scrollTop <= 96 && isHeaderSticky) classie.remove(_header, "is-sticky");
    // console.log("document scrollTop", isHeaderSticky, scrollTop);
  }

  init();
})();
