function gestionCases(caseChoisie)
{
	coche = caseChoisie.checked;
	if(caseChoisie.id == 'alerteActive')
	{
		if(coche == true) 
		{
			document.getElementById('alertePostActive').checked = false;
			document.getElementById('alerteReplyActive').checked = false;
			document.getElementById('alerteEditActive').checked = false;
			document.getElementById('alertePostActive').disabled = true;
			document.getElementById('alerteReplyActive').disabled = true;
			document.getElementById('alerteEditActive').disabled = true;
		} else 
		{
			document.getElementById('alertePostActive').disabled = false;
			document.getElementById('alerteReplyActive').disabled = false;
			document.getElementById('alerteEditActive').disabled = false;
		}
	} else if(caseChoisie.id == 'alertePostActive' || caseChoisie.id == 'alerteReplyActive' || caseChoisie.id == 'alerteEditActive')
	{
		if(coche == true)
		{
			document.getElementById('alerteActive').checked = false;
		}
		
		all_coche = document.getElementById('alertePostActive').checked && document.getElementById('alerteReplyActive').checked && document.getElementById('alerteEditActive').checked;
		if(all_coche == true)
		{
			document.getElementById('alerteActive').checked = true;
			document.getElementById('alerteActive').disabled = false;
			document.getElementById('alertePostActive').checked = false;
			document.getElementById('alerteReplyActive').checked = false;
			document.getElementById('alerteEditActive').checked = false;
			document.getElementById('alertePostActive').disabled = true;
			document.getElementById('alerteReplyActive').disabled = true;
			document.getElementById('alerteEditActive').disabled = true;
		}
	}
}