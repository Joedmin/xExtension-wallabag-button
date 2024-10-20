<?php

class WallabagButtonExtension extends Minz_Extension
{
  #[\Override]
  public function init(): void
  {
    $this->registerTranslates();

    Minz_View::appendScript($this->getFileUrl('script.js', 'js'), false, false, false);
    Minz_View::appendStyle($this->getFileUrl('style.css', 'css'));
    Minz_View::appendScript(_url('wallabagButton', 'jsVars'), false, true, false);

    $this->registerController('wallabagButton');
    $this->registerViews();
  }

  #[\Override]
  public function handleConfigureAction(): void
  {
    $this->registerTranslates();

    if (Minz_Request::isPost()) {
      $keyboard_shortcut = Minz_Request::paramString('keyboard_shortcut');
      FreshRSS_Context::userConf()->_attribute('wallabag_keyboard_shortcut', $keyboard_shortcut);
      FreshRSS_Context::userConf()->save();
    }
  }
}
