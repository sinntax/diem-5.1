<?php

require_once(dirname(__FILE__).'/helper/dmUnitTestHelper.php');
$helper = new dmUnitTestHelper();
$helper->boot('front');

$t = new lime_test(79);

$markdown = $helper->get('markdown');
dm::loadHelpers(array('Dm'));

$t->comment('Create a test page');

$page = dmDb::create('DmPage', array(
  'module'  => dmString::random(),
  'action'  => dmString::random(),
  'name'    => dmString::random(),
  'slug'    => dmString::random()
));

$page->Node->insertAsFirstChildOf(dmDb::table('DmPage')->getTree()->fetchRoot());

$t->ok($page->exists(), 'A test page has been created');

$tests = array(
  '[basic link](%source%)' => array(    // source
    'basic link',                       // ->toText
    'basic link',                       // ->brutalToText
    £link($page)->text('basic link')    // ->toHtml
  ),
  '[link with id](%source% #an_id)' => array(
    'link with id',
    'link with id',
    £link($page)->text('link with id')->set('#an_id')
  ),
  '[link with classes](%source% .a_class.another_class)' => array(
    'link with classes',
    'link with classes',
    £link($page)->text('link with classes')->set('.a_class.another_class')
  ),
  '[link with id and classes](%source% #an_id.a_class.another_class)' => array(
    'link with id and classes',
    'link with id and classes',
    £link($page)->text('link with id and classes')->set('#an_id.a_class.another_class')
  ),
  'a [basic link](%source%) and a [link with id and classes](%source% #an_id.a_class.another_class)' => array(
    'a basic link and a link with id and classes',
    'a basic link and a link with id and classes',
    sprintf('a %s and a %s',
      £link($page)->text('basic link'),
      £link($page)->text('link with id and classes')->set('#an_id.a_class.another_class')
    )
  ),
  '[link with title](%source% "this is a title")' => array(
    'link with title',
    'link with title',
    £link($page)->text('link with title')->title('this is a title')
  ),
  '[link with title, id and classes](%source% "this is a title" #an_id.a_class.another_class)' => array(
    'link with title, id and classes',
    'link with title, id and classes',
    £link($page)->text('link with title, id and classes')->title('this is a title')->set('#an_id.a_class.another_class')
  ),
  '[link with anchor](%source%#an_anchor)' => array(
    'link with anchor',
    'link with anchor',
    £link($page)->text('link with anchor')->anchor('#an_anchor')
  ),
  '[link with anchor, id and classes](%source%#an_anchor #an_id.a_class.another_class)' => array(
    'link with anchor, id and classes',
    'link with anchor, id and classes',
    £link($page)->text('link with anchor, id and classes')->anchor('#an_anchor')->set('#an_id.a_class.another_class')
  ),
  '[link with params](%source%?var1=val1&var2=val2)' => array(
    'link with params',
    'link with params',
    £link($page)->text('link with params')->params(array('var1' => 'val1', 'var2' => 'val2'))
  ),
  '[link with params, id and classes](%source%?var1=val1&var2=val2 #an_id.a_class.another_class)' => array(
    'link with params, id and classes',
    'link with params, id and classes',
    £link($page)->text('link with params, id and classes')
    ->params(array('var1' => 'val1', 'var2' => 'val2'))
    ->set('#an_id.a_class.another_class')
  ),
  '[link with anchor, params, id and classes](%source%#an_anchor?var1=val1&var2=val2 #an_id.a_class.another_class)' => array(
    'link with anchor, params, id and classes',
    'link with anchor, params, id and classes',
    £link($page)->text('link with anchor, params, id and classes')
    ->anchor('#an_anchor')
    ->params(array('var1' => 'val1', 'var2' => 'val2'))
    ->set('#an_id.a_class.another_class')
  ),
  '[link with title, anchor, params, id and classes](%source%#an_anchor?var1=val1&var2=val2 "this is a title" #an_id.a_class.another_class)' => array(
    'link with title, anchor, params, id and classes',
    'link with title, anchor, params, id and classes',
    £link($page)->text('link with title, anchor, params, id and classes')
    ->title('this is a title')
    ->anchor('#an_anchor')
    ->params(array('var1' => 'val1', 'var2' => 'val2'))
    ->set('#an_id.a_class.another_class')
  ),
);

$absoluteUrlRoot = $helper->get('request')->getAbsoluteUrlRoot();

foreach($tests as $code => $results)
{
  $sourceId = str_replace('%source%', 'page:'.$page->id, $code);
  $t->comment($sourceId);
  
  $t->is($result = $markdown->toText($sourceId), $results[0], '->toText() '.$result);
  $t->is($result = $markdown->brutalToText($sourceId), $results[1], '->brutalToText() '.$result);
  
  $t->is($result = $markdown->toHtml($sourceId), £('p.dm_first_p', $results[2]), '->toHtml() '.$result);
  
  $sourcePath = str_replace('%source%', $absoluteUrlRoot.'/'.$page->slug, $code);
  $t->comment($sourcePath);
  
  $t->is($result = $markdown->toText($sourcePath), $results[0], '->toText() '.$result);
  $t->is($result = $markdown->brutalToText($sourcePath), $results[1], '->brutalToText() '.$result);
  
  $t->is($result = $markdown->toHtml($sourcePath), str_replace('href="', 'href="http://', £('p.dm_first_p', $results[2])), '->toHtml() '.$result);
}

$page->Node->delete();