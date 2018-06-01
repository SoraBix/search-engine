import java.io.File;
import java.io.FileInputStream;
import java.io.PrintWriter;

import org.apache.tika.metadata.Metadata;
import org.apache.tika.parser.ParseContext;
import org.apache.tika.parser.html.HtmlParser;
import org.apache.tika.sax.BodyContentHandler;

public class TikaParser {

	private static final String bigFile = "/Users/sora/Desktop/Shared/big.txt";
	private static final String htmlFilesDir = "/Users/sora/Desktop/Shared/NBC_News/HTML files/";
	
	public static void main(String[] args) throws Exception {
		
		PrintWriter writer = new PrintWriter (bigFile);
		
		File htmlDir = new File(htmlFilesDir);
		int count = 0;
		
		for(File file: htmlDir.listFiles()){
			count++;
			if(count % 1000 == 0) {
				System.out.println("Parsing file number :: " + count);
			}
			
			FileInputStream is = new FileInputStream(file);
			BodyContentHandler bcHandler = new BodyContentHandler(-1);
			Metadata metaDataObj = new Metadata();
			ParseContext parseContextObj = new ParseContext();
			HtmlParser htmlParserObj = new HtmlParser();
			
			htmlParserObj.parse(is, bcHandler, metaDataObj, parseContextObj);
			
			String bodyContent = bcHandler.toString().replaceAll("\\s+", " ").replaceAll("\n", " ");
			String words[] = bodyContent.split(" ");
			
			for(String w: words)
			{
				if(w.matches("[a-zA-Z]+\\.?"))
				{
					writer.print(w + " ");
				}
			}
		}
		System.out.println("Total files parsed :: " + count);
		writer.close();
	}

}
