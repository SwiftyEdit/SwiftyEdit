import $ from "jquery";

$(function() {


    var sidebarState = sessionStorage.getItem('sidebarState');
    var sidebarHelpState = sessionStorage.getItem('sidebarHelpState');

    var windowWidth = $(window).width();

    $(window).resize(function () {
        var windowWidth = $(window).width();

        if (windowWidth < 992) { //992 is the value of $screen-md-min in boostrap variables.scss
            $('#page-sidebar-inner').addClass('sidebar-collapsed').removeClass('sidebar-expanded');
            $('#page-content').addClass('sb-collapsed').removeClass('sb-expanded');
            $('#page-sidebar').addClass('sb-collapsed').removeClass('sb-expanded');

        } else {

            if (sidebarState) {
                $('#page-sidebar-inner').addClass('sidebar-collapsed').removeClass('sidebar-expanded');
                $('#page-content').addClass('sb-collapsed').removeClass('sb-expanded');
                $('#page-sidebar').addClass('sb-collapsed').removeClass('sb-expanded');
            } else {
                $('#page-sidebar-inner').addClass('sidebar-expanded').removeClass('sidebar-collapsed');
                $('#page-content').addClass('sb-expanded').removeClass('sb-collapsed');
                $('#page-sidebar').addClass('sb-expanded').removeClass('sb-collapsed');
            }
        }
    });

    function setSidebarState(item, value) {
        sessionStorage.setItem(item, value);
    }

    function clearSidebarState(item) {
        sessionStorage.removeItem(item);
    }

    function collapseSidebar() {
        $('#page-sidebar-inner').addClass('sidebar-collapsed').removeClass('sidebar-expanded');
        $('#page-content').addClass('sb-collapsed').removeClass('sb-expanded');
        $('#page-sidebar').addClass('sb-collapsed').removeClass('sb-expanded');
        $('.caret_left').addClass('d-none');
        $('.caret_right').removeClass('d-none');
    }

    function expandSidebar() {
        $('#page-sidebar-inner').addClass('sidebar-expanded').removeClass('sidebar-collapsed');
        $('#page-content').addClass('sb-expanded').removeClass('sb-collapsed');
        $('#page-sidebar').addClass('sb-expanded').removeClass('sb-collapsed');
        $('.caret_right').addClass('d-none');
        $('.caret_left').removeClass('d-none');
    }

    function SupportCol_hide() {
        $('#collapseSupport').addClass('d-none').removeClass('col-3');
        setSidebarState('sidebarHelpState', 'hidden');
    }

    function SupportCol_show() {
        $('#collapseSupport').addClass('col-3').removeClass('d-none');
        ;
        setSidebarState('sidebarHelpState', 'expanded');
    }


    /** check sessionStorage to expand/collapse sidebar onload **/
    if (sidebarState == "collapsed") {
        collapseSidebar();
    } else {

        if (windowWidth < 992) {
            $('#page-sidebar-inner').addClass('sidebar-collapsed').removeClass('sidebar-expanded');
            $('#page-content').addClass('sb-collapsed').removeClass('sb-expanded');
            $('#page-sidebar').addClass('sb-collapsed').removeClass('sb-expanded');
        } else {

            if (sidebarState) {
                $('#page-sidebar-inner').addClass('sidebar-collapsed').removeClass('sidebar-expanded');
                $('#page-content').addClass('sb-collapsed').removeClass('sb-expanded');
                $('#page-sidebar').addClass('sb-collapsed').removeClass('sb-expanded');
            } else {
                $('#page-sidebar-inner').addClass('sidebar-expanded').removeClass('sidebar-collapsed');
                $('#page-content').addClass('sb-expanded').removeClass('sb-collapsed');
                $('#page-sidebar').addClass('sb-expanded').removeClass('sb-collapsed');
                $('.caret_right').addClass('d-none');
                $('.caret_left').removeClass('d-none');
            }
        }
    }

    if (sidebarHelpState === "hidden" || typeof sidebarHelpState === 'undefined' || sidebarHelpState === null) {
        SupportCol_hide();
    } else {
        SupportCol_show();
    }


    /** collapse the sidebar navigation **/
    $('#toggleNav').click(function () {
        if (!($('#page-sidebar-inner').hasClass('sidebar-collapsed'))) { // if sidebar is not yet collapsed
            collapseSidebar();
            setSidebarState('sidebarState', 'collapsed');
        } else {
            expandSidebar();
            clearSidebarState('sidebarState');
        }
        return false;
    })

    /** toggle the sidebar for help **/
    $('#toggleSupport').click(function () {
        if (($('#collapseSupport').hasClass('d-none'))) {
            SupportCol_show();
        } else {
            SupportCol_hide();
        }
        return false;
    })

});