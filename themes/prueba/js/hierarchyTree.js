/*global VuFind */

var hierarchyID, recordID, htmlID, hierarchyContext;

/* Utility functions */
function htmlEncodeId(id) {
  return id.replace(/\W/g, "-"); // Also change Hierarchy/TreeRenderer/JSTree.php
}
function html_entity_decode(string) {
  var hash_map = {
    '&': '&amp;',
    '>': '&gt;',
    '<': '&lt;'
  };
  var tmp_str = string.toString();

  for (var symbol in hash_map) {
    if (hash_map.hasOwnProperty(symbol)) {
      var entity = hash_map[symbol];
      tmp_str = tmp_str.split(entity).join(symbol);
    }
  }
  tmp_str = tmp_str.split('&#039;').join("'");

  return tmp_str;
}

function getRecord(id) {
  $.ajax({
    url: VuFind.path + '/Hierarchy/GetRecord?' + $.param({id: id}),
    dataType: 'html'
  })
  .done(function getRecordDone(response) {
    $('#hierarchyRecord').html(html_entity_decode(response));
    // Remove the old path highlighting
    $('#hierarchyTree a').removeClass("jstree-highlight");
    // Add Current path highlighting
    var jsTreeNode = $(":input[value='" + id + "']").parent();
    jsTreeNode.children("a").addClass("jstree-highlight");
    jsTreeNode.parents("li").children("a").addClass("jstree-highlight");
  });
}

function changeNoResultLabel(display) {
  if (display) {
    $("#treeSearchNoResults").removeClass('hidden');
  } else {
    $("#treeSearchNoResults").addClass('hidden');
  }
}

function changeLimitReachedLabel(display) {
  if (display) {
    $("#treeSearchLimitReached").removeClass('hidden');
  } else {
    $("#treeSearchLimitReached").addClass('hidden');
  }
}

var searchAjax = false;
function doTreeSearch() {
  $('#treeSearchLoadingImg').removeClass('hidden');
  var keyword = $("#treeSearchText").val();
  if (keyword.length === 0) {
    $('#hierarchyTree').find('.jstree-search').removeClass('jstree-search');
    var tree = $('#hierarchyTree').jstree(true);
    tree.close_all();
    tree._open_to(htmlID);
    $('#treeSearchLoadingImg').addClass('hidden');
  } else {
    if (searchAjax) {
      searchAjax.abort();
    }
    searchAjax = $.ajax({
      url: VuFind.path + '/Hierarchy/SearchTree?' + $.param({
        lookfor: keyword,
        hierarchyID: hierarchyID,
        type: $("#treeSearchType").val()
      }) + "&format=true"
    })
    .done(function searchTreeAjaxDone(data) {
      if (data.results.length > 0) {
        $('#hierarchyTree').find('.jstree-search').removeClass('jstree-search');
        var jstree = $('#hierarchyTree').jstree(true);
        jstree.close_all();
        for (var i = data.results.length; i--;) {
          var id = htmlEncodeId(data.results[i]);
          jstree._open_to(id);
        }
        for (var j = data.results.length; j--;) {
          var tid = htmlEncodeId(data.results[j]);
          $('#hierarchyTree').find('#' + tid).addClass('jstree-search');
        }
        changeNoResultLabel(false);
        changeLimitReachedLabel(data.limitReached);
      } else {
        changeNoResultLabel(true);
      }
      $('#treeSearchLoadingImg').addClass('hidden');
    });
  }
}

function buildJSONNodes(xml) {
  var jsonNode = [];
  $(xml).children('item').each(function xmlTreeChildren() {
    var content = $(this).children('content');
    var id = content.children("name[class='JSTreeID']");
    var name = content.children('name[href]');
    jsonNode.push({
      id: htmlEncodeId(id.text()),
      text: name.text(),
      li_attr: { recordid: id.text() },
      a_attr: {
        href: name.attr('href'),
        title: name.text()
      },
      type: name.attr('href').match(/\/Collection\//) ? 'collection' : 'record',
      children: buildJSONNodes(this)
    });
  });
  return jsonNode;
}

function buildTreeWithXml(cb) {
  $.ajax({
    url: VuFind.path + '/Hierarchy/GetTree',
    data: {
      hierarchyID: hierarchyID,
      id: recordID,
      context: hierarchyContext,
      mode: 'Tree'
    }
  })
  .done(function getTreeDone(xml) {
    var nodes = buildJSONNodes($(xml).find('root'));
    cb.call(this, nodes);
  });
}

$(document).ready(function hierarchyTreeReady() {
  
  /*SCB 2017/01/24 => View context button does not work*/
  // Code for the search button
/*hierarchyID = $("#hierarchyTree").find(".hiddenHierarchyId")[0].value;
  recordID = $("#hierarchyTree").find(".hiddenRecordId")[0].value;
  var context = $("#hierarchyTree").find(".hiddenContext")[0].value;
   */
	/*END SCB 2017/01/24*/
	if ( $("#hierarchyTree").find(".hiddenHierarchyId").length > 0 )
		hierarchyID = document.getElementsByClassName('hiddenHierarchyId')[0].value;
		
	if ( $("#hierarchyTree").find(".hiddenRecordId").length > 0 )
		recordID = document.getElementsByClassName('hiddenRecordId')[0].value;
		
	if ( $("#hierarchyTree").find(".hiddenContext").length > 0 )	
		var context = document.getElementsByClassName('hiddenContext')[0].value;
	

  $("#hierarchyTree")
    .bind("ready.jstree", function (event, data) {
      var tree = $("#hierarchyTree").jstree(true);
      tree.select_node(recordID.replace(':', '-'));
      tree._open_to(recordID.replace(':', '-'));

      if (context == "Collection") {
        getRecord(recordID.replace('-', ':'));
      }

      $("#hierarchyTree").bind('select_node.jstree', function(e, data) {
        if (context == "Record") {
          window.location.href = data.node.a_attr.href;
        } else {
          getRecord(data.node.id.replace('-', ':'));
        }
      });
      /**SCB 24/01/2017 => Require tree open***/
      //tree.open_all();
      /**END SCB 24/01/2017***/
      // Scroll to the current record
      if ($('#hierarchyTree').parents('#modal').length > 0) {
        var hTree = $('#hierarchyTree');
        var offsetTop = hTree.offset().top;
        var maxHeight = Math.max($(window).height() - offsetTop - 50, 200);
        hTree.css('max-height', maxHeight + 'px').css('overflow', 'auto').css('overflow-x','hidden');
        hTree.animate({
          scrollTop: $('.jstree-clicked').offset().top - offsetTop + hTree.scrollTop() - 50
        }, 1500);
      } else {
        $('html,body').animate({
          scrollTop: $('.jstree-clicked').offset().top - 50
        }, 1500);
      }
    })
    .jstree({
      'plugins': ['search','types'],
      'core' : {
        'data' : function (obj, cb) {
          $.ajax({
            'url': path + '/Hierarchy/GetTree',
            'data': {
              'hierarchyID': hierarchyID,
              'id': recordID,
              'context': context,
              'mode': 'Tree'
            },
            'success': function(xml) {
              var nodes = buildJSONNodes($(xml).find('root'));
              cb.call(this, nodes);
            }
          })
        },
        'themes' : {
          // Se pega con el slider de años
          //'url': path + '/themes/bootstrap3/js/vendor/jsTree/themes/default/style.css'
        }
      },
      'types' : {
        'record': {
          'icon':'fa fa-file'
        },
        'collection': {
          'icon':'fa fa-folder'
        }
      }
    });

  $('#treeSearch').removeClass('hidden');
  $('#treeSearch [type=submit]').click(doTreeSearch);
  $('#treeSearchText').keyup(function (e) {
    var code = (e.keyCode ? e.keyCode : e.which);
    if(code == 13 || $(this).val().length == 0) {
      doTreeSearch();
    }
  });
  
});
