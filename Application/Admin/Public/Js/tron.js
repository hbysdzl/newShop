$("tr.tron").mouseover(function(){//鼠标移入事件
	$(this).find("td").css("backgroundColor","#F1FAFC");
});

$("tr.tron").mouseout(function(){//鼠标移除事件
	$(this).find("td").css("backgroundColor","#FFF");
});

