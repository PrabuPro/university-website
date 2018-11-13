import $ from 'jquery';

class MyNotes{
    constructor(){
        this.events();
    }

    events(){
        //below line of code will lesten to the future delete note buttons too.
        //create new will add new notes which are not listen by '$(".delete-note").on("click", this.deleteNote.bind(this));'
        //since ubove code wont lesten future added note buttons will note work
        //adding below type of lestener contain the whole my-note list item which contain all note including future ones.
        $("#my-notes").on("click", ".delete-note" , this.deleteNote);
        $("#my-notes").on("click", ".edit-note" ,this.editNote.bind(this));
        $("#my-notes").on("click", ".update-note" ,this.updateNote.bind(this));
        $(".submit-note").on("click", this.createNote.bind(this));
        

    }

    //methodes are here
    editNote(e) {
        var thisNote = $(e.target).parents("li");
        if(thisNote.data("state") == "editable") {
            this.makeNoteReadOnly(thisNote);
            
        }
        else{
            this.makeNoteEditable(thisNote);
           
        }

    }

    makeNoteEditable(thisNote){
        thisNote.find(".edit-note").html('<i class="fa fa-times" area-hidden="true"></i> Cancel');
        thisNote.find(".note-title-field, .note-body-field").removeAttr("readonly").addClass("note-active-field");
        thisNote.find(".update-note").addClass("update-note--visible");
        thisNote.data("state", "editable");
    }

    makeNoteReadOnly(thisNote){
        thisNote.find(".edit-note").html('<i class="fa fa-pencil" area-hidden="true"></i> Edit');
        thisNote.find(".note-title-field, .note-body-field").attr("readonly","readonly").removeClass("note-active-field");
        thisNote.find(".update-note").removeClass("update-note--visible");
        thisNote.data("state", "cancel");
    }

    //
    deleteNote(e){
        //assign data-id(post id)
        var thisNote = $(e.target).parents("li");
        
        
        $.ajax({
            //below is to send nonce with the request header
            beforeSend: (xhr) => {
                xhr.setRequestHeader('X-WP-nonce', universityData.nonce)
            },
            //to make the post ID dynamic we can setup and echo for each post from page-my-note.php. Then we can pass
            //the object we clicked through fucntion call. Since it is alway passing when we call function we need to 
            //grap it like above""deleteNote(e)"
            //we put id using data() fuction. when we use data() we don't have to specity the data-id. just puttin id is 
            //enogh
            url: universityData.root_url + '/wp-json/wp/v2/note/' + thisNote.data('id'),
            type: 'DELETE',
            success:(response) => {
                thisNote.slideUp();
                console.log("congrats");
                console.log(response);
                if (response.userNoteCount < 5 ) {
                    $(".note-limit-message").removeClass("active");
                 }
            },
            error:(response) => {
                console.log("sorry");
                console.log(response);
            }
        })
    }

    updateNote(e) {
        //assign data-id(post id)
        var thisNote = $(e.target).parents("li");

        //asign title and content to ourUpdatePost
        var ourUpdatedpost = {
            'title' : thisNote.find(".note-title-field").val(),
            'content' : thisNote.find(".note-body-field").val()
        }

        $.ajax({

            beforeSend: (xhr) => {
                xhr.setRequestHeader('X-WP-nonce', universityData.nonce)
            },

            url: universityData.root_url + '/wp-json/wp/v2/note/' + thisNote.data('id'),
            type: 'POST',
            //sending updated content
            data: ourUpdatedpost,
            success: (response) => {
                this.makeNoteReadOnly(thisNote);
                console.log("congrats");
                console.log(response);
            },
            error: (response) => {
                console.log("sorry");
                console.log(response);
            }
        })
    }

    createNote(e) {

        //asign title and content to ourUpdatePost
        //need to put status as publish in order to publish the new post. other wise 
        //new note remain as a draft
        var ourNewPost = {
            'title': $(".new-note-title").val(),
            'content': $(".new-note-body").val(),
            'status' : 'publish'
        }

        $.ajax({

            beforeSend: (xhr) => {
                xhr.setRequestHeader('X-WP-nonce', universityData.nonce)
            },

            url: universityData.root_url + '/wp-json/wp/v2/note/',
            type: 'POST',
            //sending updated content
            data: ourNewPost,
            success: (response) => {
                $(".new-note-title, .new-note-body").val();

                //below code will put the new post in to html page once create it
                //noted that it referse response object in oder to get related data.
                //in this success section of ajax pass the all the data what we wanted to put the new created code
                $(`
                    <li data-id="${response.id}">
                        <input readonly class="note-title-field" value="${response.title.raw}" >
                        <span class="edit-note"><i class="fa fa-pencil" area-hidden="true"></i> Edit</span>
                        <span class="delete-note"><i class="fa fa-trash-o" area-hidden="true"></i> Delete</span>
                        <textarea readonly class="note-body-field">${response.content.raw}</textarea>
                        <span class="update-note btn btn--blue btn--small"><i class="fa fa-arrow-right" area-hidden="true"></i> Save</span>
                    </li>
                `).prependTo("#my-notes").hide().slideDown();
                console.log("congrats");
                console.log(response);
            },
            error: (response) => {
                if (response.responseText == "You have reached your note limits") {
                    $(".note-limit-message").addClass("active");
                }
                console.log("sorry");
                console.log(response);
            }
        })
    }
    
}
    

export default MyNotes;