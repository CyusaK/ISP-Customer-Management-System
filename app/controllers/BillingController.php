class Customer {
    private $db;

    public function __construct($database) {
        $this->db = $database;
    }

    public function register($name, $email, $phone) {
        $query = "INSERT INTO customers (name, email, phone)
                  VALUES (?, ?, ?)";
        // Prepared statement logic here
    }

    public function getAllCustomers() {
        // Fetch all customers logic
    }
}