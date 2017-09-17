{

/** <?php
// The `maybeJSON` function is not needed in PHP because its return semantics
// are the same as `json_decode`
?> **/

function maybeJSON( s ) {
	try {
		return JSON.parse( s );
	} catch (e) {
		return null;
	}
}

}

Block_List
  = pre:(!Block_Start a:. { /** <?php return $a; ?> **/ return a })*
    WS* ts:(t:Token WS* { /** <?php return $t; ?> **/ return t })*
    post:.*
  { /** <?php
    $blocks = [];
    if ( ! empty( $pre ) ) { $blocks[] = $pre; }
    $blocks = array_merge( $blocks, $ts );
    if ( ! empty( $post ) ) { $blocks[] = $post; }

    return $blocks;
    ?> **/

    return [
      pre.length && { blockName: 'core/freeform', innerHtml: pre.join('') },
      ...ts,
      post.length && { blockName: 'core/freeform', innerHtml: post.join('') },
    ].filter( a => a )
  }

Token
  = Tag_More
  / Block_Void
  / Block_Balanced

Tag_More
  = "<!--" WS* "more" customText:(WS+ text:$((!(WS* "-->") .)+) { /** <?php return $text; ?> **/ return text })? WS* "-->" noTeaser:(WS* "<!--noteaser-->")?
  { /** <?php
    return array(
       'blockName' => 'core/more',
       'attrs' => array(
         'customText' => $customText,
         'noTeaser' => (bool) $noTeaser
       ),
       'rawContent' => ''
    );
    ?> **/
    return {
      blockName: 'core/more',
      attrs: {
        customText: customText,
        noTeaser: !! noTeaser
      },
      rawContent: ''
    }
  }

Block_Void
  = "<!--" WS+ "wp:" blockName:Block_Name WS+ attrs:(a:Block_Attributes WS+ {
    /** <?php return $a; ?> **/
    return a;
  })? "/-->"
  {
    /** <?php
    return array(
      'blockName'  => $blockName,
      'attrs'      => $attrs,
      'rawContent' => '',
    );
    ?> **/

    return {
      blockName: blockName,
      attrs: attrs,
      rawContent: ''
    };
  }

Block_Balanced
  = s:Block_Start children:Block_Matter+ e:Block_End & {
    /** <?php return $s['blockName'] === $e['blockName']; ?> **/
    return s.blockName === e.blockName;
  }
  {
    /** <?php
    $innerBlocks = array_filter( $children, function( $a ) {
      return ! is_string( $a );
    } );

    $innerHtml = array_filter( $children, function( $a ) {
      return is_string( $a );
    } );

    return array(
      'blockName'  => $s['blockName'],
      'attrs'      => $s['attrs'],
      'innerBlocks'  => $innerBlocks,
      'innerHtml'  => implode( '', $innerHtml ),
    );
    ?> **/

    var innerBlocks = children.filter( a => 'string' !== typeof a );
    var innerHtml = children.filter( a => 'string' === typeof a ).join('');

    return {
      blockName: s.blockName,
      attrs: s.attrs,
      innerBlocks: innerBlocks,
      innerHtml: innerHtml
    };
  }

Block_Matter
  = Token
  / (!Block_End a:. { /** <?php return $a; ?> **/ return a })

Block_Start
  = "<!--" WS+ "wp:" blockName:Block_Name WS+ attrs:(a:Block_Attributes WS+ {
    /** <?php return $a; ?> **/
    return a;
  })? "-->"
  {
    /** <?php
    return array(
      'blockName' => $blockName,
      'attrs'     => $attrs,
    );
    ?> **/

    return {
      blockName: blockName,
      attrs: attrs
    };
  }

Block_End
  = "<!--" WS+ "/wp:" blockName:Block_Name WS+ "-->"
  {
    /** <?php
    return array(
      'blockName' => $blockName,
    );
    ?> **/

    return {
      blockName: blockName
    };
  }

Block_Name
  = $(ASCII_Letter (ASCII_AlphaNumeric / "/" ASCII_AlphaNumeric)*)

Block_Attributes
  = attrs:$("{" (!("}" WS+ """/"? "-->") .)* "}")
  {
    /** <?php return json_decode( $attrs, true ); ?> **/
    return maybeJSON( attrs );
  }

ASCII_AlphaNumeric
  = ASCII_Letter
  / ASCII_Digit
  / Special_Chars

ASCII_Letter
  = [a-zA-Z]

ASCII_Digit
  = [0-9]

Special_Chars
  = [\-\_]

WS
  = [ \t\r\n]

Newline
  = [\r\n]

_
  = [ \t]

__
  = _+

Any
  = .
