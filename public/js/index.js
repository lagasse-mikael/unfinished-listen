let lastClickedPlayButton;

$(document).ready(function() {
    $("a").click(function() {
        let btn = $(this)
        btn.addClass('a_simplebounce')
        setTimeout(function() {
            btn.removeClass('a_simplebounce')
            console.log("OK?");
        },700)
    })
})

function playPreviewTrack(url,clickedButton)
{
    let audioSource = document.getElementById("generalAudioPlayerSource")
    let audioPlayer = document.getElementById("generalAudioPlayer")

    audioSource.src = url;
    
    if(clickedButton == lastClickedPlayButton)
    {
        clickedButton.text = "🎵";
        audioPlayer.pause();
        lastClickedPlayButton = null;
        return;
    }
    
    if(lastClickedPlayButton)
        lastClickedPlayButton.text = "🎵";
    
    lastClickedPlayButton = clickedButton;
    
    audioPlayer.volume = 0.45
    audioPlayer.load()
    audioPlayer.play()
    
    clickedButton.text = "🎶";
    setTimeout(function() {
        clickedButton.text = "🎵";
    },30000)
}

function postSong(trackID,clickedButton)
{
    clickedButton.text = "📡";
    const POST_URL = (window.location.origin + window.location.pathname).replace('search','postSong');

    $.post(POST_URL,
        {
            "trackID": trackID
        },function(data, status) {
            if(data == 201)
            {
                clickedButton.text = "✅";
            }
            else{
                clickedButton.text = "❌";
                console.log(data);
            }
        })
}