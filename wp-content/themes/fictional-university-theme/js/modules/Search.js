import $ from 'jquery';

class Search {
    //1. describe and create/initiate our object
    constructor() {
        this.addSearchHTML();
        this.resultsDiv = $("#search-overlay_results");
        this.openButton = $(".js-search-trigger");
        this.closeButton = $(".search-overlay__close");
        this.searchOverlay = $(".search-overlay");
        this.searchField = $("#search-term");
        this.events();
        this.isOverlayOpen = false;
        this.isSpinnerVisible = false;
        this.previousValue;
        this.typingTimer;

    }

    //2.events
    events() {
        this.openButton.on("click", this.opentOverlay.bind(this));
        this.closeButton.on("click", this.closeOverlay.bind(this));
        $(document).on("keydown", this.keypressDispatcher.bind(this));
        this.searchField.on("keyup", this.typingLogic.bind(this));
    }


    //3.methode(functions, actions..)
    typingLogic() {
        if (this.searchField.val() != this.previousValue) {
            clearTimeout(this.typingTimer);
            if (this.searchField.val()) {
                if (!this.isSpinnerVisible) {
                    this.resultsDiv.html('<div class="spinner-loader"></div>');
                    this.isSpinnerVisible = true;
                }
                this.typingTimer = setTimeout(this.getResults.bind(this), 750);
                //here we are setting up setTimeout function in order to delay our search string. but it  will run for 
                //each letters when when we type a word. in order to avoid that we call clearTimeout() function and 
                //that will call every time when we hit a key and cleartime counting by setTypeOut whict lawer that 2000 miliseconds

            } else {
                this.resultsDiv.html('');
                this.isSpinnerVisible = false;
            }
        }
        this.previousValue = this.searchField.val();
    }

    getResults() {
        /*
        1. getJSON is the function we use to communicate with json files. 
        2. In order to avoid bind functions we can use ES6 arrow function "posts => {}". this will not change the value of "this."" key word
        3. In "this.resultDiv.html(``)" we have use `` in order to enter html.It helps us to maintain the
           nice format of html with enter in to new line.If we are not using `` there is a syntax error
           when bracking lines.
        4. we use ${} represent the native part of javascript. within this {} brackets we can code real javascript
           code. This is like enter some php code in html.
        5. in order to loop through an array we can use .map() function. It will run at each item in a array.
           And also we can use .join('') in order to avoid the comma which generate by map() function while
           looping through array.
        6. We cant use if condition inside ``. but we can use Tenary operator.
        7. in order to tell the correct url for javascript we can use wp_localize_script() in functions.php. javascript
           dose not know what the correnct REST API url is. But wordpress know. So we tell the url from wordpress to
           javascript(see funcitons.php)

           -- *** Synchronous methode of handling json *** --
        8. we can concatinate pages and post url arrays and get the results using .concat(other_array)
        
            -- *** Asynchronous methode of handling json ***--
        9.  Here we use $.when().then() methodes in jquery
        10. Inside of the when() methode we are going to use getJSON() method in order to send the request REST API url
        11. Typically getJSON() massege have 2 arguments for url and methode. In this case no need to define a
            methode once recieved a responce since then() methode will handle methode after recieved a responce.
        12. We can define 2 arrays to store the responce for each getJSON requests and these 2 arrays will contain json objects for each 
            requests seperately.
        13. In concat() section for those arrays we define in order to store json objects, we have to refer the 
            1st item of the array. Since .when() give us other several details(like information about success or fail) we should the     
        14. Within our then() method we can do exception handling when we have lost connection. We can put a comma
            and enter out metode within ES6 array functions. 
        */


        $.getJSON(universityData.root_url + '/wp-json/university/v1/search?term=' + this.searchField.val(), (results) => {
            this.resultsDiv.html(`
                <div class="row">
                    <div class="one-third">
                        <h2 class="search-overlay__section-title" > General Information </h2>
                        ${results.generalInfo.length ? '<ul class="link-list min-list ">' : '<p>No general information matches that search.</p>'}
                            ${results.generalInfo.map(item => `<li><a href="${item.permalink}">${item.title}</a> ${item.postType == 'post' ? `by ${item.authorName}` : ''} </li>`).join('')}
                        ${results.generalInfo.length ? '</ul>' : ''}
                    </div>
                    <div class="one-third">
                        <h2 class="search-overlay__section-title" >Programs</h2>
                        ${results.programs.length ? '<ul class="link-list min-list ">' : `<p>No Programs matches that search. <a href="${universityData.root_url}/programs">View all programs</a></p>`}
                            ${results.programs.map(item => `<li><a href="${item.permalink}">${item.title}</a></li>`).join('')}
                        ${results.programs.length ? '</ul>' : ''}
                        <h2 class="search-overlay__section-title" >Professors</h2>
                        ${results.professors.length ? '<ul class="professor-cards">' : `<p>No Professors matches that search.</p>`}
                            ${results.professors.map(item => `
                                <li class="professor-card__list-item" >
                                    <a class="professor-card" href="${item.permalink}" >
                                        <img class="professor-card__image" src="${item.image}" >
                                        <span class="professor-card__name">${item.title}< /span>    
                                    </a> 
                                </li>
                            `).join('')}
                        ${results.professors.length ? '</ul>' : ''}
                    </div>
                    <div class="one-third">
                        <h2 class="search-overlay__section-title" >Campuses</h2>
                        ${results.campuses.length ? '<ul class="link-list min-list ">' : `<p>No  matches that search. <a href="${universityData.root_url}/campuses">View all campuses</a></p>`}
                            ${results.campuses.map(item => `<li><a href="${item.permalink}">${item.title}</a> ${item.postType == 'post' ? `by ${item.authorName}` : ''} </li>`).join('')}
                            ${results.campuses.length ? '</ul>' : ''}
                            <h2 class="search-overlay__section-title" >Events</h2>
                            ${results.events.length ? '' : `<p>No  events that search. <a href="${universityData.root_url}/events">View all events</a></p>`}
                                ${results.events.map(item => `                                   
                                    <div class="event-summary">
                                        <a class="event-summary__date t-center" href="${item.permalink}">
                                            <span class="event-summary__month">${item.month}</span>
                                            <span class="event-summary__day">${item.day}</span>
                                        </a>
                                        <div class="event-summary__content">
                                            <h5 class="event-summary__title headline headline--tiny">
                                                <a href="${item.permalink}">${item.title}</a>
                                            </h5>
                                            <p>${item.description}<a href="${item.permalink}" class="nu gray">Learn more</a>
                                            </p>
                                        </div>
                                    </div>
                                    `).join('')}
                    </div>
                </div>
            `);

            this.isSpinnerVisible = false;
        });
    }

    keypressDispatcher(e) {

        if (e.keyCode == 83 && !this.isOverlayOpen && !$("input, textarea").is(':focus')) {
            this.opentOverlay();
        }
        if (e.keyCode == 27 && this.isOverlayOpen) {
            this.closeOverlay();
        }
    }

    opentOverlay() {
        this.searchOverlay.addClass("search-overlay--active");
        $("body").addClass("body-no-scroll");
        this.searchField.val('');
        setTimeout(() => this.searchField.focus(), 301 );
        this.isOverlayOpen = true;
    }

    closeOverlay() {
        this.searchOverlay.removeClass("search-overlay--active");
        $("body").removeClass("body-no-scroll");
        this.isOverlayOpen = false;

    }

    addSearchHTML() {
        $("body").append(`
            <div class="search-overlay">
                <div class = "search-overlay__top">
                    <div class = "container">
                        <i class = "fa fa-search search-overlay__icon fa-3x" aria-hidden = "true"></i>
                         <input type = "text" class = "search-term" placeholder = "what are you looking for?" id = "search-term" >
                        <i class = "fa fa-window-close search-overlay__close fa-3x" aria-hidden = "true"></i> 
                    </div> 
                </div>

                <div class ="container">
                    <div id = "search-overlay_results" >
                    </div> 
                </div>
            </div>
          `);
    }

}

export default Search;