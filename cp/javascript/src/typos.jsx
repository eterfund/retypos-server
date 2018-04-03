import React from 'react';
import ReactDOM from 'react-dom';

import 'bootstrap'
import 'bootstrap/dist/css/bootstrap.min.css';

import SiteList from "./components/SiteList";

// Get json array of typos and render component application
$.ajax({
    url: window.baseUrl + "/users/typos/getSiteList",
}).done((sites) => {
    renderSiteList(sites);
}).fail((error) => {
    console.log(error);
});

function renderSiteList(sites) {
    ReactDOM.render(<SiteList sites={sites}/>, document.getElementById("root"));
}