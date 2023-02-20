using System;
using System.Text.Json;
using System.IO;
using System.Text;
using System.Xml;
using System.IO.Compression;

namespace Lab1
{
    class Person
    {
        public string Name { get; set; }
        public int Age { get; set; }
        public Person(string name, int age)
            {
            Name = name;
            Age = age;
            }

    }

    class User
    {
        public string Name { get; set; }
        public int Age { get; set; }
        public string Company { get; set; }
     
    }

    class Program
    {
        public static void Compress(string sourceFile, string compressedFile)
        {
            // поток для чтения исходного файла
            using (FileStream sourceStream = new FileStream(sourceFile, FileMode.OpenOrCreate))
            {
                // поток для записи сжатого файла
                using (FileStream targetStream = File.Create(compressedFile))
                {
                    // поток архивации
                    using (GZipStream compressionStream = new GZipStream(targetStream, CompressionMode.Compress))
                    {
                        sourceStream.CopyTo(compressionStream); // копируем байты из одного потока в другой
                        Console.WriteLine("Сжатие файла {0} завершено. Исходный размер: {1}  сжатый размер: {2}.",
                            sourceFile, sourceStream.Length.ToString(), targetStream.Length.ToString());
                    }
                }
            }
        }

        public static void Decompress(string compressedFile, string targetFile)
        {
            // поток для чтения из сжатого файла
            using (FileStream sourceStream = new FileStream(compressedFile, FileMode.OpenOrCreate))
            {
                // поток для записи восстановленного файла
                using (FileStream targetStream = File.Create(targetFile))
                {
                    // поток разархивации
                    using (GZipStream decompressionStream = new GZipStream(sourceStream, CompressionMode.Decompress))
                    {
                        decompressionStream.CopyTo(targetStream);
                        Console.WriteLine("Восстановлен файл: {0}", targetFile);
                    }
                }
            }
        }
        static void Main(string[] args)
        {
            DriveInfo[] drives = DriveInfo.GetDrives();

            foreach (DriveInfo d in drives)
            {
                Console.WriteLine("Название диска: {0}", d.Name);
                Console.WriteLine("Тип диска: {0}", d.DriveType);
                if (d.IsReady == true)
                {
                    Console.WriteLine("Метка: {0}", d.VolumeLabel);
                    Console.WriteLine("Объем диска: {0}", d.TotalSize);
                    Console.WriteLine("Свободное пространство на диске: {0}", d.TotalFreeSpace);
                    Console.WriteLine("Тип файловой системы: {0}", d.DriveFormat);
                }
                Console.WriteLine();
            }

            string p = @"test.txt";
            Console.WriteLine("Введите текст для записи в файл:");
            string txt = Console.ReadLine();

            //запись в файл
            try
            {
                using (FileStream fs = File.Create(p))
                {
                    //преобразуем строку в байты
                    byte[] info = new UTF8Encoding(true).GetBytes(txt);
                    fs.Write(info, 0, info.Length);
                }

                using (StreamReader sr = File.OpenText(p))
                {
                    string s = "";
                    while ((s = sr.ReadLine()) != null)
                    {
                        Console.WriteLine("Текст из файла: {0}", s);
                    }
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine(ex.ToString());
            }

            string curTxt = p;
            Console.WriteLine(File.Exists(curTxt) ? "Нажмите 9 чтобы удалить файл" : "Файл не существует");
            string num1 = Console.ReadLine();

            switch (num1)
            {
                case "9":
                    File.Delete(p);
                    Console.WriteLine("Файл удален\n");
                    break;
                default:
                    Console.WriteLine("Файл сохранен\n");
                    break;
            }

            Person tom = new Person("Smith", 45);
            string json = JsonSerializer.Serialize(tom);
            using (FileStream fs = new FileStream("person.json", FileMode.OpenOrCreate))
            {
                byte[] info = System.Text.Encoding.Default.GetBytes(json);
                fs.Write(info, 0, info.Length);
                Console.WriteLine("Текст записан в файл");
            }

            using (FileStream fs = File.OpenRead("person.json"))
            {
                byte[] info = new byte[fs.Length];
                fs.Read(info, 0, info.Length);
                string textFromFile = System.Text.Encoding.Default.GetString(info);
                Console.WriteLine($"Текст из файла: {textFromFile}");
            }
            string j = @"person.json";
            string curJson = j;
            Console.WriteLine(File.Exists(curJson) ? "Нажмите 9 чтобы удалить файл" : "Файл не существует");
            string num2 = Console.ReadLine();

            switch (num2)
            {
                case "9":
                    File.Delete(j);
                    Console.WriteLine("Файл удален\n");
                    break;
                default:
                    Console.WriteLine("Файл сохранен\n");
                    break;
            }

            Console.WriteLine("Текст из XML файла:");
            XmlDocument xDoc = new XmlDocument();
            xDoc.Load("user.xml");
            XmlElement xRoot = xDoc.DocumentElement;
            
            

            //создаем новый элемент
            XmlElement userElem = xDoc.CreateElement("user");

            //создаем атрибут name
            XmlAttribute nameAtr = xDoc.CreateAttribute("name");

            //создаем элемент age
            XmlElement ageElem = xDoc.CreateElement("age");

            //создаем элемент company
            XmlElement companyElem = xDoc.CreateElement("company");

            //создаем текстовые занчения для атрибута и эллементов
            XmlText nameText = xDoc.CreateTextNode("Pavel");
            XmlText ageText = xDoc.CreateTextNode("35");
            XmlText companyText = xDoc.CreateTextNode("Telegram");

            //добавляем узлы
            nameAtr.AppendChild(nameText);
            ageElem.AppendChild(ageText);
            companyElem.AppendChild(companyText);

            //добавляем атрибут name
            userElem.Attributes.Append(nameAtr);

            //добавляем элементы age и company
            userElem.AppendChild(ageElem);
            userElem.AppendChild(companyElem);

            // добавляем в корневой элемент новый элемент 
            xRoot.AppendChild(userElem);

            // сохраняем изменения xml-документа в файл
            xDoc.Save("user.xml");

            xDoc.Load("user.xml");
            if (xRoot != null)
            {
                foreach (XmlElement xnode in xRoot)
                {
                    XmlNode atr = xnode.Attributes.GetNamedItem("name");
                    Console.WriteLine($"Name: {atr.Value}");

                    foreach (XmlNode childNode in xnode.ChildNodes)
                    {
                        if (childNode.Name == "age")
                        {
                            Console.WriteLine($"Age: {childNode.InnerText}");
                        }
                        if (childNode.Name == "company")
                        {
                            Console.WriteLine($"Company: {childNode.InnerText}");
                        }
                    }
                }
                Console.WriteLine();
            }
            XmlDocument xDocD = new XmlDocument();
            xDocD.Load("user.xml");
            XmlElement xRootD = xDocD.DocumentElement;
            XmlNode firstNode = xRootD.FirstChild;
            if (firstNode != null) xRootD.RemoveChild(firstNode);
            xDocD.Save("user.xml");

            Compress("compress.xml", "zipfile.zip");

            // Создать архив в формате zip
            // Добавить файл, выбранный пользователем, в архив
            Console.WriteLine("Имя файла для сжатия:");
            var f_name = Console.ReadLine();
            if (f_name == null)
                return;

            Compress(f_name, "zipfile2.zip");

            Console.WriteLine("Разархивирование zipfile2.zip и вывод данных о нем.");
            Decompress("zipfile2.zip", "new_" + f_name);

            // Удалить файл и архив
            File.Delete("out-zip.zip");
            File.Delete("uncomp_" + f_name);
        }
    }
}
