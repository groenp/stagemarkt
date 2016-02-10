// 2nd try - PG
//
$(document).ready(function () {

  // SEARCH SECTION //

  // clear button in search fields 
  $(".srch-input").keyup(function () {                                            // show 'x' when typing into field, hide when deleting
    $(this).parent().parent().find(".srch-clear").toggle(Boolean($(this).val()));
  });
  $(".srch-clear").toggle(Boolean($(this).parent().find("input").val()));         // when loading
  $(".srch-clear").click(function () {                                            // clear function
    $(this).parent().find("input").eq(0).val('').focus();
    $(this).hide();
  });

  // droplists (all items in the list need a space behind them)
  $(".dropdown-menu a").click(function () {                                       // show content in button of selected a
    $(this).parent().parent().parent().find("button").html($(this).text()).append("<span class='caret'></span>");
    if ($(this).parent().parent().parent().find("button")[0].id == "srch-loc-btn") $("#srch-abroad").find("input[type=checkbox]").prop('checked', false); // uncheck the abroad button
  });

  // checkbox exclusive to location (TODO: not clear if this means other section needs to be ignored or wiped)
  $("#srch-abroad").click(function () {                                         // when checkbox switched on, wipe loc + dist=0
    if ($(this).find("input[type=checkbox]").prop('checked') == true) {
      $("#srch-loc").val('');
      $("#srch-loc-btn").html("+ 0km <span class='caret'></span>");
    }
  });
  $("#srch-loc").blur(function () {
    if ($(this).val() != "") {
      $("#srch-abroad").find("input[type=checkbox]").prop('checked', false);
    }
  });
});