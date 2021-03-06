<?php
namespace Gt\Dom;

use Gt\Dom\TokenList;

class ElementTest extends \PHPUnit_Framework_TestCase {

public function testQuerySelector() {
	$document = new HTMLDocument(test\Helper::HTML_MORE);
	$pAfterH2 = $document->querySelector("h2+p");
	$aWithinP = $pAfterH2->querySelector("a");

	$a = $document->querySelector("p>a");

	$this->assertInstanceOf(Element::class, $pAfterH2);
	$this->assertInstanceOf(Element::class, $aWithinP);
	$this->assertInstanceOf(Element::class, $a);
	$this->assertSame($a, $aWithinP);
}

public function testQuerySelectorAll() {
	$document = new HTMLDocument(test\Helper::HTML_MORE);
	$pCollection = $document->documentElement->querySelectorAll("p");
	$pNodeList = $document->documentElement->getElementsByTagName("p");

	$this->assertEquals($pNodeList->length, $pCollection->length);
}

public function testMatches() {
	$document = new HTMLDocument(test\Helper::HTML_MORE);
	$p = $document->getElementsByClassName("plug")->item(0);

	$this->assertTrue($p->matches("p"));
	$this->assertTrue($p->matches("p.plug"));
	$this->assertTrue($p->matches("body>p:nth-of-type(3)"));
	$this->assertTrue($p->matches("p:nth-of-type(3)"));
	$this->assertTrue($p->matches("body>*"));
	$this->assertFalse($p->matches("div"));
	$this->assertFalse($p->matches("body>p:nth-of-type(4)"));
}

public function testChildElementCount() {
	$document = new HTMLDocument(test\Helper::HTML_MORE);
	// There is 1 text node within the document.
	$this->assertGreaterThan(
		$document->body->childElementCount,
		$document->body->childNodes->length
	);
	$this->assertEquals(
		$document->body->children->length,
		$document->body->childElementCount
	);
}

public function testElementClosest() {
	$document = new HTMLDocument(test\Helper::HTML_NESTED);

	$p = $document->querySelector('.inner-list p');
	$this->assertInstanceOf('\Gt\Dom\Element', $p);

	$innerList = $document->querySelector('.inner-list');
	$this->assertInstanceOf('\Gt\Dom\Element', $innerList);

	$closestUl = $innerList->closest('ul');
	$this->assertEquals($innerList, $closestUl);

	$container = $p->closest('.container');
	$this->assertInstanceOf('\Gt\Dom\Element', $container);

	$nonExistentClosestElement = $p->closest('br');
	$this->assertNull($nonExistentClosestElement);

	$innerPost = $document->querySelector("div.post.inner");
	$innerListItem = $document->querySelector(".inner-item-1");
	$outerPost = $document->querySelector("div.post.outer");
	$this->assertInstanceOf(Element::class, $innerPost);
	$this->assertInstanceOf(Element::class, $outerPost);

	$closestDivToInnerListItem = $innerListItem->closest("div");
	$closestDivToInnerPost = $innerPost->closest("div");
// ..the inner post should match itself, as it is a div.
	$this->assertSame($closestDivToInnerPost, $innerPost);
// ..but the inner list item should match up the tree to the outer post
// ..missing the other divs in the tree.
	$this->assertSame($closestDivToInnerListItem, $outerPost);
}

public function testValueGetter() {
	$document = new HTMLDocument(test\Helper::HTML_VALUE);

	$select = $document->getElementById('select');
	$this->assertEquals('1', $select->value);
	$select->value = '2';
	$this->assertEquals('2', $select->value);

	$select = $document->getElementById('select_optgroup');
	$this->assertEquals('3', $select->value);
	$select->value = '4';
	$this->assertEquals('4', $select->value);

	$select = $document->getElementById('select_selected');
	$this->assertEquals('2', $select->value);

	$select = $document->getElementById('select_empty');
	$this->assertEquals('', $select->value);
	$select->value = 'dummy';
	$this->assertEquals('', $select->value);
}

public function testInnerHTML() {
	$document = new HTMLDocument(test\Helper::HTML_MORE);
	$p = $document->querySelector(".link-to-twitter");
	$this->assertContains("<a href=", $p->innerHTML);
	$this->assertContains("Greg Bowler", $p->innerHTML);
	$this->assertNotContains("<p", $p->innerHTML);

	$p->innerHTML = "This is <strong>very</strong> important!";
	$this->assertInstanceOf(Element::class, $p->querySelector("strong"));
	$this->assertContains("This is", $p->textContent);
	$this->assertContains("very", $p->textContent);
	$this->assertEquals("very", $p->querySelector("strong")->textContent);
}

public function testOuterHTML() {
	$document = new HTMLDocument(test\Helper::HTML_MORE);
	$p = $document->querySelector(".link-to-twitter");
	$this->assertContains("<a href=", $p->outerHTML);
	$this->assertContains("Greg Bowler", $p->outerHTML);
	$this->assertContains("<p", $p->outerHTML);
	$this->assertContains("</p>", $p->outerHTML);
	$this->assertNotContains("<h2", $p->outerHTML);
	$this->assertNotContains("name=\"forms\">", $p->outerHTML);
}

public function testClassListProperty() {
	$document = new HTMLDocument(test\Helper::HTML_MORE);
	$element = $document->getElementById("who");
	$this->assertInstanceOf(TokenList::class, $element->classList);

	$this->assertTrue($element->classList->contains("m-before-p"));
	$this->assertFalse($element->classList->contains("nothing"));
}

public function testClassNameProperty() {
	$document = new HTMLDocument(test\Helper::HTML_MORE);
	$element = $document->getElementById("who");
	$this->assertInternalType("string", $element->className);

	$this->assertContains("m-before-p", $element->className);
	$this->assertNotContains("nothing", $element->className);
}

public function testIdProperty() {
	$document = new HTMLDocument(test\Helper::HTML_MORE);
	$element = $document->getElementById("who");
	$this->assertEquals("who", $element->id);
}

public function testTagNameProperty() {
	$document = new HTMLDocument(test\Helper::HTML_MORE);
	$element = $document->getElementsByTagName("form")[0];
	$this->assertEquals("form", $element->tagName);
}

public function testValueProperty() {
	$document = new HTMLDocument(test\Helper::HTML_MORE);
	$paragraph = $document->getElementById("who");
	$this->assertNull($paragraph->value);

	$input = $document->querySelector("form input[name=who]");
	$this->assertEquals("Scarlett", $input->value);

	$input->value = "Sparky";
	$this->assertEquals("Sparky", $input->getAttribute("value"));
}

public function testRemove() {
	$document = new HTMLDocument(test\Helper::HTML_MORE);
	$bodyChildrenCount = count($document->body->children);
	$paragraph = $document->querySelector("p");
	$paragraph->remove();
	$this->assertCount($bodyChildrenCount - 1, $document->body->children);
}

}#
