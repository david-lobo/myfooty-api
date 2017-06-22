/**
 * scrape-fixtures.js runs first
 * 
 * Go through all the competitions, and scrape data using competition id
 *
 * Save data in json format in the pending folder (page0.json etc.)
 *
 * Example URLS:
 *
 * Premier League - Comp Id = 1, CompSeasons = 54
 * https://footballapi.pulselive.com/football/fixtures?comps=1&compSeasons=54&page=0&pageSize=40&statuses=U,L&altIds=true&startDate=2016-10-05
 * https://footballapi.pulselive.com/football/broadcasting-schedule/fixtures?comps=1&fixtureIds=14112%2C14110%2C14111%2C14115%2C14118%2C14119%2C14113%2C14116%2C14117%2C14114%2C14121%2C14120%2C14122%2C14124%2C14125%2C14128%2C14129%2C14126%2C14127%2C14123%2C14136%2C14132%2C14133%2C14137%2C14138%2C14139%2C14130%2C14131%2C14134%2C14135%2C14141%2C14142%2C14147%2C14149%2C14143%2C14140%2C14144%2C14146%2C14148%2C14145&pageSize=100&page=0
 *
 * Champions League - Comp Id = 2, CompSeasons = 66
 * https://footballapi.pulselive.com/football/fixtures?comps=2&compSeasons=66&page=0&pageSize=40&statuses=U,L&altIds=true&startDate=2016-10-05
 * https://footballapi.pulselive.com/football/broadcasting-schedule/fixtures?comps=1&fixtureIds=15753%2C15755%2C15754%2C15756%2C15757%2C15758%2C15759%2C15760%2C15761%2C15762%2C15763%2C15764%2C15765%2C15766%2C15767%2C15768%2C15771%2C15769%2C15770%2C15772%2C15773%2C15774%2C15775%2C15776%2C15777%2C15778%2C15779%2C15780%2C15781%2C15782%2C15783%2C15784%2C15786%2C15785%2C15787%2C15788%2C15789%2C15790%2C15791%2C15792&pageSize=100&page=0
 *
 * Europa Leage - Comp Id = 3, CompSeasons = 70
 * https://footballapi.pulselive.com/football/fixtures?comps=3&compSeasons=70&page=1&pageSize=40&statuses=U,L&altIds=true&startDate=2016-10-05
 *
 * FA Cup - comp Id = 4, CompSeasons = 71
 * https://footballapi.pulselive.com/football/fixtures?comps=4&compSeasons=32&page=0&pageSize=40&statuses=U,L&altIds=true&startDate=2016-10-05
 * https://footballapi.pulselive.com/football/broadcasting-schedule/fixtures?comps=1&fixtureIds=&pageSize=100&page=0
 *
 * EFL Cup - Comp Id = 5, CompSeasons - 56
 * https://footballapi.pulselive.com/football/fixtures?comps=5&compSeasons=56&page=0&pageSize=40&statuses=U,L&altIds=true&startDate=2016-10-05
 * https://footballapi.pulselive.com/football/broadcasting-schedule/fixtures?comps=1&fixtureIds=16222%2C16223%2C16224%2C16225%2C16227%2C16228%2C16229%2C16226&pageSize=100&page=0
 *
 **/

var casper = require('casper').create({
    verbose: true,
    logLevel: "debug"
});

var fs = require('fs');
const querystring = require('querystring');

console.log("scrape-fixtures.js running");

// custom functions in this script
var processNextPage, processNextPageWithWait, getFixtureData;
var pageLimitSafe = 10; // Never exceed this limit - to avoid DOS
//var currentPage = 0;  // Page reached in processing of pagination
var maxWaitingInterval = 10; // max secs to wait
var waitingIntervals = [31200, 4060, 5420, 6180, 7900, 870, 9300, 10300, 32450, 4171, 5134, 6203, 7514, 8411, 9991, 10450]; // Random wait values to avoid suspicion

//Not sure why - maybe cached on Wednesday's??
var startDateForFixtures = '2016-10-05';
startDateForFixtures = '';

var path = './config/settings.json';

var settings = fs.read(path);

settings = JSON.parse(settings);

var scrapeLiveUrls = settings.scrape_live_urls;
var configs = settings.configs;

//require('utils').dump(scrapeLiveUrls);
//require('utils').dump(configs);

var configs = settings.configs;
var config = configs.live;

if (!scrapeLiveUrls) {
    config = configs.local;
	var waitingIntervals = [1];
    console.log("setting local mode");
} else {
    console.log("setting live mode");
}

var competitions = config.competitions;
var competitionsDict;
var urls = config.urls;

// Logging
var filesWritten = [];
var fixturesFound = 0;
var pageInfo = {};
var fixturesData = {};

// Array of fixtures to use for requesting broadcast schedule data
var fixtureIds = {}
var data;

var fixturesPathAbsolute = storagePath(config.paths.fixtures);

function storagePath(path) {
    return settings.storage_path + '/' + path;
}

// generate a random number between 0 (inclusive) and max (inclusivee)
function randomNumber(max) {
    var randomNumber = Math.floor(Math.random()*(max + 1))
    return randomNumber;
}

function buildFixturesURL(competitionId, currentPage) {
	var fixturesURL = urls.fixtures;
	var startDate = window.startDateForFixtures;
	
http://local.test.com/footballapi-pulselive/fixtures.php?comps=2&compSeasons=66&page=1&pageSize=40&statuses=U%2CL&altIds=true&startDate=2016-10-05
    var comp = window.competitionsDict[competitionId];

    var params = '?comps=' + competitionId + '&compSeasons=' + comp.compSeasons + '&page=' + currentPage + '&pageSize=40&statuses=U,L&altIds=true'; 
	
    if (startDate != null && startDate.length > 0) {
        params += '&startDate=' + startDate;
    }
    var wsurl = fixturesURL + params;
	
	console.log(wsurl);
	return wsurl
}

function buildBroadcastScheduleURL(compId, fixtureIds) {
    var fixturesURL = urls.schedule;
    var fixtureIdsString = fixtureIds.join(','); 

    //?comps=1&fixtureIds=14112%2C&pageSize=100&page=0'
    var params = querystring.stringify({ 
        comps: compId, 
        fixtureIds: fixtureIdsString,
        pageSize: 100,
        page: 0});

    //var params = '?comps=' + competitionId + '&compSeasons=54&page=' + window.currentPage + '&pageSize=40&statuses=U,L&altIds=true&startDate=' + startDate; 

    var wsurl = fixturesURL + '?' + params;
    
    console.log(wsurl);
    return wsurl
}

function buildFixturesDirectoryPath(compId) {
    var lastChar = fixturesPathAbsolute.substr(fixturesPathAbsolute.length - 1);
    var sep = lastChar == '/' ? '' : '/';

    var fixturesPath = fixturesPathAbsolute + sep + compId;
    return fixturesPath;
}

function removeFixturesDirectory() {
    fs.rmdirSync(path)
}

getFixtureData = function(competitionId, currentPage) { 
	console.log("getFixtureData");
	var wsurl = buildFixturesURL(competitionId, currentPage);
    data = casper.evaluate(function(wsurl) {
    	
        return JSON.parse(__utils__.sendAJAX(wsurl, 'GET', null, false));
    }, {wsurl: wsurl});
    casper.then(function() {

    if (data != null) {
       //require('utils').dump(data);
       window.fixturesData = data;
       window.pageInfo = data.pageInfo;
       //console.log(window.pageInfo.numPages);

       processFixtureIds(competitionId, data, currentPage);

       var fixturesPath = buildFixturesDirectoryPath(competitionId);
       var filename = 'page' + (currentPage) + ".json";
       saveDataToFile(data, fixturesPath, filename);
    } else {
        console.log("getFixtureData return null", competitionId, currentPage);
    }
});
}

getBroadcastScheduleData = function(compId, fixtureIds, currentPage) { 

    var bsurl = buildBroadcastScheduleURL(compId, fixtureIds);

    var bsData = casper.evaluate(function(bsurl) {
        
        return JSON.parse(__utils__.sendAJAX(bsurl, 'GET', null, false));
    }, {bsurl: bsurl});
    casper.then(function() {

    console.log("return from getBroadcastScheduleData api call", bsData);
    
    //require('utils').dump(bsData);

    if (bsData != null) {
        var fixturesPath = buildFixturesDirectoryPath(compId);

        var schedulePath = fixturesPath + '/broadcasts';

        var filename = 'page' + currentPage + '.json';
        saveDataToFile(bsData, schedulePath, filename);
    }

   console.log("return from getBroadcastScheduleData");
});
}


saveDataToFile = function(dataToSave, pathToSave, filename) {

	console.log("saveFixturesToFile 1.1");
    //links = this.evaluate(getLinks);
    //fixturesFound += links.length;
    jsonStr = JSON.stringify(dataToSave);

    // Make the directory tree - date name indexed
    //var d = new Date();
    //var month = (d.getMonth() + 1);
    //var day = d.getDate();

    //month = month < 10 ? "0" + month : month;
    //day = day < 10 ? "0" + day : day;

    //var path = "data/list/" + d.getFullYear() + month  + day;

    //var currentPage = dataToSave.pageInfo.page;

    if(fs.makeTree(pathToSave))
      console.log('"'+ pathToSave +'" was created.');
    else
      console.log('"'+ pathToSave +'" is NOT created.');

    // Set the filename and path
    //filename = 'page' + (currentPage) + ".json";

    var lastChar = pathToSave.substr(pathToSave.length - 1);
    var sep = lastChar == '/' ? '' : '/';

    filepath = pathToSave + sep + filename;

    console.log("file path is " + filepath);
    //casper.log("Writing " + dataToSave.content.length + " links to file '" + filepath + "'", 'info');
    casper.log("Writing data to file '" + filepath + "'", 'info');

    fs.write(filepath, jsonStr, 'w');

    filesWritten.push(filepath);
};


processFixtureIds = function(competitionId, data, currentPage) {
	console.log("processFixtures", competitionId, currentPage);
	window.fixtureIds[data.pageInfo.page] = [];

    var fids = [];

	for (var i = 0; i < data.content.length; i++) {
		var fixture = data.content[i];

		window.fixtureIds[data.pageInfo.page].push(fixture.id);
        fids.push(fixture.id);
		//require('utils').dump(fixture.id);
	}

    getBroadcastScheduleData(competitionId, fids, currentPage);
}

processNextPage = function(competitionId, currentPage) {
    console.log("processNextPage(" + competitionId + ", " + currentPage + ")");
    casper.then(function() { 
        getFixtureData(competitionId, currentPage);
    }).then(function() {

        var numPages = window.fixturesData.pageInfo.numPages;
        console.log("Number of pages: ", numPages);
        console.log("Current page: ", currentPage);

        currentPage++;
        require('utils').dump(window.fixturesData.pageInfo);



        // recursive part - this repeats for all pages in pagination
        if (currentPage < pageLimitSafe && currentPage < numPages) {
            this.then(function() {
                processNextPageWithWait(competitionId, currentPage);
            });
        } 
    });
}

processNextPageWithWait = function(competitionId, currentPage) {
    console.log("processNextPageWithWait(" + competitionId + ", "+ currentPage +")");
	var waitingInterval = waitingIntervals[randomNumber(waitingIntervals.length - 1)];


	if (isNaN(waitingInterval)) {
		waitingInterval = 11000;
	}

	console.log("Waiting for " + waitingInterval/1000 + " seconds", "info");
	casper.wait(waitingInterval, function() { 
        processNextPage(competitionId, currentPage);
    });
}

console.log(fixturesPathAbsolute);

// Remove all files and dir 'data/fixtures'
fs.removeTree(fixturesPathAbsolute);

//saveDataToFile('test', fixturesPathAbsolute, 'test.txt');
//console.log(fixturesPathAbsolute);

casper.start(urls.home);
casper.userAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X)');



window.competitionsDict = {};

window.competitions.forEach(function(competition) {
    window.competitionsDict[competition["id"]] = competition;
    casper.then(function() {
        processNextPageWithWait(competition["id"], 0);
    });
});

require('utils').dump(window.competitionsDict);

casper.run(function() {
	//require('utils').dump(window.fixtureIds);
	this.exit();
});