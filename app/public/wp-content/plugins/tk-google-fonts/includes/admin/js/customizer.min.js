(function(n){wp.customize('h1_font',function(o){o.bind(function(o){if(o=='none'){n('h1, h1 a').css('font-family','')}
else{n('h1, h1 a').css('font-family',o)}})});wp.customize('h2_font',function(o){o.bind(function(o){if(o=='none'){n('h2, h2 a').css('font-family','')}
else{n('h2, h2 a').css('font-family',o)}})});wp.customize('h3_font',function(o){o.bind(function(o){if(o=='none'){n('h3, h3 a').css('font-family','')}
else{n('h3, h3 a').css('font-family',o)}})});wp.customize('h4_font',function(o){o.bind(function(o){if(o=='none'){n('h4, h4 a').css('font-family','')}
else{n('h4, h4 a').css('font-family',o)}})});wp.customize('h5_font',function(o){o.bind(function(o){if(o=='none'){n('h5, h5 a').css('font-family','')}
else{n('h5, h5 a').css('font-family',o)}})});wp.customize('h6_font',function(o){o.bind(function(o){if(o=='none'){n('h6, h6 a').css('font-family','')}
else{n('h6, h6 a').css('font-family',o)}})});wp.customize('body_text',function(o){o.bind(function(o){if(o=='none'){n('body, p').css('font-family','')}
else{n('body, p').css('font-family',o)}})});wp.customize('blockquotes',function(o){o.bind(function(o){if(o=='none'){n('blockquote, blockquote p, blockquote p a').css('font-family','')}
else{n('blockquote, blockquote p, blockquote p a').css('font-family',o)}})})})(jQuery);