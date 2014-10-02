<?php
  /*
   * viewmpage.php
   * viewmentee page
   * information about a single mentee
   */

class ViewMPage implements Page
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function display()
  {
    if (!isset($_GET['id']) || empty($_GET['id']))
    {
      return "Keine ID angegeben.";
    }
    $id = $_GET['id'];
    $mentee = $this->db->getMenteeById($id);
    if (empty($mentee))
    {
      $id = htmlspecialchars($id);
      return "Es existiert kein Mentee mit der ID <tt>$id</tt>.";
    }

    $rv = array();
    $rv['page'] = 'viewmentee';
    $rv['data'] = array();
    $rv['data']['mentee'] = $mentee;
    $rv['data']['articles']      = $this->db->getArticlesByMenteeId($id);
    foreach ($rv['data']['articles'] as &$a)
    {
      $a['title'] = $this->db->get_article_name_by_id($a['ma_page_id']);
    }
    $rv['data']['article_count'] = count($rv['data']['articles']);
    $rv['data']['activity']      = $this->db->is_user_active($mentee['mentee_user_id']);
    $rv['data']['mentors'] = $this->db->getMentorsByMentee($id);
    $rv['data']['mentee_edit_count'] = $this->db->get_user_edit_count($mentee['mentee_user_id']);
    $rv['title']   = "Neuling {$rv['data']['mentee']['mentee_user_name']}";
    $rv['heading'] = "Neuling <em>{$rv['data']['mentee']['mentee_user_name']}</em>";

    return $rv;
  }
}
