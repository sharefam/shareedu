import Sidebar from '../component/side-bar.js';	
import './admin-app';
let data = $('.cd-layout-sidebar').data('nodes');

new Sidebar({data});
$('.navbar-right .dropdown-toggle').on('click', function(){
  $('.navbar-right .dropdown-menu').not($(this).next('ul')[0]).hide();
  $(this).next('ul').toggle();
});
$(document).on('click',function(){
  $('.navbar-right .dropdown-toggle').next('ul').hide();
});
