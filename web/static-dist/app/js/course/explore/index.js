webpackJsonp(["app/js/course/explore/index"],{bf96baae4c1bc6c67d72:function(e,o,a){"use strict";echo.init(),$("#live, #free").on("click",function(e){var o=$(e.currentTarget);$("input:checkbox").attr("checked",!1),o.attr("checked",!0),window.location.href=o.val()}),$(".open-course-list").on("click",".section-more-btn a",function(e){var o=$(void 0).attr("data-url");$.ajax({url:o,dataType:"html",success:function(e){var o=$(".open-course-list .course-block,.open-course-list .section-more-btn",$(e)).fadeIn("slow");$(".section-more-btn").remove(),$(".open-course-list").append(o),echo.init()}})}),$(".js-filter-action").on("click",function(){var e=$(this).children(".es-icon");e.hasClass("es-icon-keyboardarrowup")?($(".js-tab-filter").removeClass("no-bor"),e.removeClass("es-icon-keyboardarrowup").addClass("es-icon-keyboardarrowdown")):($(".js-tab-filter").addClass("no-bor"),e.removeClass("es-icon-keyboardarrowdown").addClass("es-icon-keyboardarrowup")),$(".js-tabs-container").toggleClass("hidden")})}},["bf96baae4c1bc6c67d72"]);