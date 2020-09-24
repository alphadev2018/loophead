// Example starter JavaScript for disabling form submissions if there are invalid fields
(function () {
  'use strict'

  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  var forms = document.querySelectorAll('.needs-validation')
  // Loop over them and prevent submission
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }

        form.classList.add('was-validated')
      }, false)
    })


    let clickableItem = document.querySelectorAll('ul.category_list > li, ul.child-category-list > li');
    console.log(clickableItem.length);
    clickableItem.forEach(function(item){
        item.addEventListener("click", handleToggle);
    });
  
    function handleToggle(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        this.classList.toggle('active');
    }

})()



    // var checks = document.querySelectorAll(".parent-tree input[type=checkbox]");

    // for(var i = 0; i < checks.length; i++){
    //     checks[i].addEventListener( 'change', function() {
    //         if(this.checked) {
    //             showChildrenChecks(this);
    //         } else {
    //             hideChildrenChecks(this)
    //         }
    //     });
    // }

    // function showChildrenChecks(elm) {
    //     console.log(elm.parentNode.parentNode);

    //     var pN = elm.parentNode.parentNode;
    //     var childCheks = pN.children;
        
    //     for(var i = 0; i < childCheks.length; i++){
    //         if(hasClass(childCheks[i], 'child-tree')){
    //             childCheks[i].classList.add("active");      
    //         }
    //     }
        
    //     }

    // function hideChildrenChecks(elm) {
    //     var pN = elm.parentNode.parentNode;
    //     var childCheks = pN.children;
        
    //     for(var i = 0; i < childCheks.length; i++){
    //         if(hasClass(childCheks[i], 'child-tree')){
    //             childCheks[i].classList.remove("active");      
    //         }
    //     }
        
    // }

    // function hasClass(elem, className) {
    //     return new RegExp(' ' + className + ' ').test(' ' + elem.className + ' ');
    // }



    var x, i, j, l, ll, selElmnt, a, b, c;
    /*look for any elements with the class "custom-select":*/
    x = document.getElementsByClassName("custom-select");
    l = x.length;
    for (i = 0; i < l; i++) {
      selElmnt = x[i].getElementsByTagName("select")[0];
      ll = selElmnt.length;
      /*for each element, create a new DIV that will act as the selected item:*/
      a = document.createElement("DIV");
      a.setAttribute("class", "select-selected");
      a.innerHTML = selElmnt.options[selElmnt.selectedIndex].innerHTML;
      x[i].appendChild(a);
      /*for each element, create a new DIV that will contain the option list:*/
      b = document.createElement("DIV");
      b.setAttribute("class", "select-items select-hide");
      for (j = 1; j < ll; j++) {
        /*for each option in the original select element,
        create a new DIV that will act as an option item:*/
        c = document.createElement("DIV");
        c.innerHTML = selElmnt.options[j].innerHTML;
        c.addEventListener("click", function(e) {
            /*when an item is clicked, update the original select box,
            and the selected item:*/
            var y, i, k, s, h, sl, yl;
            s = this.parentNode.parentNode.getElementsByTagName("select")[0];
            sl = s.length;
            h = this.parentNode.previousSibling;
            for (i = 0; i < sl; i++) {
              if (s.options[i].innerHTML == this.innerHTML) {
                s.selectedIndex = i;
                h.innerHTML = this.innerHTML;
                y = this.parentNode.getElementsByClassName("same-as-selected");
                yl = y.length;
                for (k = 0; k < yl; k++) {
                  y[k].removeAttribute("class");
                }
                this.setAttribute("class", "same-as-selected");
                break;
              }
            }
            h.click();
        });
        b.appendChild(c);
      }
      x[i].appendChild(b);
      a.addEventListener("click", function(e) {
          /*when the select box is clicked, close any other select boxes,
          and open/close the current select box:*/
          e.stopPropagation();
          closeAllSelect(this);
          this.nextSibling.classList.toggle("select-hide");
          this.classList.toggle("select-arrow-active");
        });
    }
    function closeAllSelect(elmnt) {
      /*a function that will close all select boxes in the document,
      except the current select box:*/
      var x, y, i, xl, yl, arrNo = [];
      x = document.getElementsByClassName("select-items");
      y = document.getElementsByClassName("select-selected");
      xl = x.length;
      yl = y.length;
      for (i = 0; i < yl; i++) {
        if (elmnt == y[i]) {
          arrNo.push(i)
        } else {
          y[i].classList.remove("select-arrow-active");
        }
      }
      for (i = 0; i < xl; i++) {
        if (arrNo.indexOf(i)) {
          x[i].classList.add("select-hide");
        }
      }
    }
    /*if the user clicks anywhere outside the select box,
    then close all select boxes:*/
    document.addEventListener("click", closeAllSelect);