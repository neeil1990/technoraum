# ���������
��� ������ �������������� ��������� � �������������� ���������, ���� �� �������� ���������� ��������, � ������, �������������� �� `\Rover\Fadmin\Options` � �������� `public $message` �������� ��������� ������ `\Rover\Fadmin\Engine\Message`. ���� ��������� ����� ������: 
	
	public function addOk($message)
	public function addError($message)

������ ��������� ��������� �� �������� � ���������� ���������� �������� ��������������. ����� ��� ��������� ��������� ��� ��������� �������� ������, ������������� �  ������������.

������ ��� ������ `Rover\Fadmin\TestOptions`:

    $options = Rover\Fadmin\TestOptions::getInstance();
    $options->message->addOk('�� � �������');
    $options->message->addError('�� ���-�� �� ���');